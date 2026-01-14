// resources/js/services/companyInternships.js
import axios from 'axios';

const COMPANY_INTERNSHIPS_ENDPOINT = '/api/company/internships';
const COMPANY_ACCEPT_ENDPOINT = '/api/internship/change-status/acceptance';

/**
 * Fetch zoznam praxí vytvorených pre firmu.
 * Backend: InternshipController@companyCreatedInternships
 *
 * @returns {Promise<Array>}
 */
export async function fetchCompanyInternships() {
    const { data } = await axios.get(COMPANY_INTERNSHIPS_ENDPOINT);
    return Array.isArray(data) ? data : [];
}

/**
 * Potvrdenie praxe firmou (CREATED → ACCEPTED)
 * Backend: InternshipController@changeStatus
 *
 * @param {number} internshipId
 * @returns {Promise<any>}
 */
export async function acceptCompanyInternship(internshipId) {
    const { data } = await axios.post(COMPANY_ACCEPT_ENDPOINT, {
        internship_id: internshipId,
        is_positive: true,
    });

    return data;
}

const COMPANY_REJECT_ENDPOINT = '/api/internship/change-status/acceptance';

export async function rejectCompanyInternship(internshipId, note) {
    const { data } = await axios.post(COMPANY_REJECT_ENDPOINT, {
        internship_id: internshipId,
        is_positive: false,
        note,
    });

    return data;
}
