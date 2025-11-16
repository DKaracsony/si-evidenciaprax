<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Company;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\CompanyOwnerProfile;
use App\Services\CompanyActivationService;
use App\Services\MailSender;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    protected $register_type;
    protected $data;
    protected $form_types = ['student_form', 'company_form'];

    public function __construct(private RoleService $roles, private CompanyActivationService $activationService) {}

    public function handleRegister(Request $request)
    {
        $this->register_type = $request->input('form_type');
        $this->data = $request->all();
        $validated = $this->validateByType();
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $created_user = $this->register_type === $this->form_types[0]
            ? $this->registerStudent()
            : $this->registerCompany();

        return response()->json(['message' => $created_user[0], 'user' => $created_user[1]], $created_user[2]);
    }

    public function validateByType()
    {
        if (!in_array($this->register_type, $this->form_types))
            return response()->json(['message' => __('registration.FORM_TYPE_ERROR')], 422);

        $common = [
            'first_name'    => ['required','string','min:2','max:50'],
            'last_name'     => ['required','string','min:2','max:50'],
            'title_before'  => ['nullable','string','max:30'],
            'phone_number'  => ['required','string','min:8','max:20'],
            'city'          => ['required','string','min:2','max:100'],
            'street'        => ['required','string','min:2','max:100'],
            'house_number'  => ['required','integer','min:1','digits_between:1,10'],
            'postal_code'   => ['required','string','min:3','max:10'],
            'country'       => ['required','integer','exists:countries,id'],
        ];

        $rules = $this->register_type === $this->form_types[0]
            ? array_merge($common, [
                'student_email' => ['required','string','max:255','email',
                    'regex:/^[a-z0-9]+(?:\.[a-z0-9]+){1,2}@student\.ukf\.sk$/i',
                    'unique:users,email',
                ],
                'faculty'       => ['required','integer','exists:faculties,id'],
                'email'         => ['required','email','max:255','unique:student_profiles,personal_email'],
            ])
            : array_merge($common, [
                'title_after'     => ['nullable','string','max:30'],
                'company_name'    => ['required','string','min:2','max:150'],
                'role_at_company' => ['nullable','string','max:50'],
                'description'     => ['nullable','string','max:800'],
                'website'         => ['nullable','string','max:255'],
                'email'           => ['required','email','max:255','unique:users,email'],
            ]);

        $validator = Validator::make($this->data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('registration.VALIDATION_FAILED'),
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $this->data = $validator->validated();
        return true;
    }

    public function registerStudent()
    {
        $random_password = bin2hex(random_bytes(4));

        $role_id = $this->roles->all()->firstWhere('name', Role::STUDENT)?->id;
        if (!$role_id) return [__('registration.ROLE_STUDENT_NOT_FOUND'), null, 422];

        $user = User::create([
            'first_name'    => $this->data['first_name'],
            'last_name'     => $this->data['last_name'],
            'title_before'  => $this->data['title_before'] ?? null,
            'email'         => $this->data['student_email'],
            'password_hash' => bcrypt($random_password),
            'phone_number'  => $this->data['phone_number'],
            'role_id'       => $role_id,
        ]);

        $address = Address::create([
            'city'          => $this->data['city'],
            'street'        => $this->data['street'],
            'house_number'  => $this->data['house_number'],
            'postal_code'   => $this->data['postal_code'],
            'country_id'    => $this->data['country'],
        ]);

        StudentProfile::create([
            'personal_email'     => $this->data['email'],
            'faculty_id'        => $this->data['faculty'],
            'address_id'        => $address->id,
            'student_user_id'   => $user->id,
        ]);

        $mailSender = new MailSender(
            'student_registration',
            [$this->data['student_email']],
            [
                'user'             => $user,
                'temporaryPassword'=> $random_password,
                'loginURL'         => rtrim(url('/login'), '/'),
            ]
        );

        $mailSender->send();

        $user->load([
            'studentProfile.address.country',
            'studentProfile.faculty',
        ]);

        return [__('registration.STUDENT_REGISTERED'), $user, 201];
    }

    public function registerCompany()
    {
        $role_id = $this->roles->all()->firstWhere('name', Role::COMPANY)?->id;
        if (!$role_id) {
            return [__('registration.ROLE_COMPANY_NOT_FOUND'), null, 422];
        }

        $user = User::create([
            'first_name'    => $this->data['first_name'],
            'last_name'     => $this->data['last_name'],
            'title_before'  => $this->data['title_before'] ?? null,
            'title_after'   => $this->data['title_after'] ?? null,
            'email'         => $this->data['email'],
            'password_hash' => null,
            'phone_number'  => $this->data['phone_number'],
            'role_id'       => $role_id,
            'active'        => false,
        ]);

        $address = Address::create([
            'city'         => $this->data['city'],
            'street'       => $this->data['street'],
            'house_number' => $this->data['house_number'],
            'postal_code'  => $this->data['postal_code'],
            'country_id'   => $this->data['country'],
        ]);

        $company = Company::create([
            'name'        => $this->data['company_name'],
            'description' => $this->data['description'] ?? null,
            'website'     => $this->data['website'] ?? null,
            'address_id'  => $address->id,
        ]);

        CompanyOwnerProfile::create([
            'is_active'             => false,
            'company_id'            => $company->id,
            'company_user_id'       => $user->id,
            'company_activation_id' => null,
            'role_at_company'       => $this->data['role_at_company'] ?? null,
        ]);

        $this->activationService->createAndSendActivation($user, $company);

        $user->load(['companyOwnerProfile.company.address']);

        return [__('registration.COMPANY_REGISTERED_PENDING_ACTIVATION'), $user, 201];
    }
}
