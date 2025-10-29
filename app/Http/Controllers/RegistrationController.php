<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    protected $register_type;
    protected $data;
    protected $form_types = ['student_form', 'company_form'];

    public function __construct(private RoleService $roles) {}

    public function handleRegister(Request $request)
    {
        $this->register_type = $request->input('form_type');
        $this->data = $request->all();

        //return response()->json(['message' => ], 501);

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
            'email'         => ['required','email','max:255','unique:users,email'],
            'phone_number'  => ['required','string','min:8', 'max:20'],
            'city'          => ['required','string','min:2', 'max:100'],
            'street'        => ['required','string','min:2', 'max:100'],
            'house_number'  => ['required', 'integer', 'min:1', 'digits_between:1,10'],
            'postal_code'   => ['required','string','min:3', 'max:10'],
            'country'       => ['required','integer','exists:countries,id'],
        ];

        $rules = $this->register_type === $this->form_types[0]
            //STUDENT
            ? array_merge($common, [
                'student_email' => ['required', 'string', 'max:255', 'email',
                    // aspoň 1 bodka - najviac 2, len písmená/čísla v segmentoch pred @ a doména musí byť student.ukf.sk
                    'regex:/^[a-z0-9]+(?:\.[a-z0-9]+){1,2}@student\.ukf\.sk$/i',
                    'unique:student_profiles,student_email',
                ],
                'faculty'       => ['required','integer','exists:faculties,id'],
            ])
            //COMPANY
            : array_merge($common, [
                //TODO: implement Atus other validations for company
            ]);

        $validator = Validator::make($this->data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('registration.VALIDATION_FAILED'),
                'errors'  => $validator->errors()->all()
            ], 422);
        }

        $this->data = $validator->validated();
        return true;
    }

    public function registerStudent()
    {
        $random_password = bin2hex(random_bytes(4));

        $role_id = $this->roles->all()->firstWhere('name', 'študent')?->id;
        if (!$role_id) return [__('registration.ROLE_STUDENT_NOT_FOUND'), null, 422];

        $user = User::create([
            'first_name'    => $this->data['first_name'],
            'last_name'     => $this->data['last_name'],
            'title_before'  => $this->data['title_before'] ?? null,
            'email'         => $this->data['email'],
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
            'student_email'     => $this->data['student_email'],
            'faculty_id'        => $this->data['faculty'],
            'address_id'        => $address->id,
            'student_user_id'   => $user->id,
        ]);

        $user->load([
            'studentProfile.address.country',
            'studentProfile.faculty',
        ]);

        return [__('registration.STUDENT_REGISTERED'), $user, 201];
    }

    public function registerCompany()
    {
        //TODO: implement Atus - without password, password will be set later if activation was successful
    }
}
