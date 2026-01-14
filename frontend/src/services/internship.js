// resources/js/services/internship.js
import axios from 'axios';

// Endpoints podľa routes/api.php
const STUDENT_INTERNSHIPS_ENDPOINT = '/api/student/internships';
const STUDENT_INTERNSHIP_ENDPOINT = '/api/student/internship';
const STUDENT_INTERNSHIP_DETAIL_ENDPOINT = '/api/student/internship-detail';

/**
 * Typ jedného záznamu z InternshipController@index
 *
 * @typedef {Object} StudentInternshipListItem
 * @property {number} id
 * @property {string} start_date
 * @property {string} date_to
 * @property {string|null} description
 * @property {boolean} is_draft
 * @property {string|null} submitted_at
 * @property {Object|null} company
 * @property {Object|null} semester
 * @property {{name: string, changed_at: string}|null} status
 * @property {Array} documents
 */

/**
 * Fetch zoznam praxí prihláseného študenta.
 * Route: GET /api/student/internships
 *
 * @returns {Promise<StudentInternshipListItem[]>}
 */
export async function fetchStudentInternships() {
    const { data } = await axios.get(STUDENT_INTERNSHIPS_ENDPOINT);
    // data je priamo pole z InternshipController@index
    return data;
}

/**
 * Detail praxe – InternshipController@show
 * Route: GET /api/student/internship-detail/{id}
 */
export async function fetchStudentInternshipDetail(id) {
    const { data } = await axios.get(`${STUDENT_INTERNSHIP_DETAIL_ENDPOINT}/${id}`);
    return data;
}

/**
 * Vytvorenie / submit praxe – InternshipController@store
 * Route: POST /api/student/internship
 *
 * @param {Object} payload - musí obsahovať is_draft + ostatné polia
 */
export async function createOrUpdateStudentInternship(payload) {
    const { data } = await axios.post(STUDENT_INTERNSHIP_ENDPOINT, payload);
    // backend vracia { message, internship, ...optional PDF info }
    return data;
}

/**
 * Stiahnutie PDF dohody
 * Route: GET /api/student/internship-detail/{internship}/agreement-pdf
 */
export async function downloadStudentInternshipAgreementPdf(id) {
    return await axios.get(
        `${STUDENT_INTERNSHIP_DETAIL_ENDPOINT}/${id}/agreement-pdf`,
        {
            responseType: 'blob',
        }
    );
}
