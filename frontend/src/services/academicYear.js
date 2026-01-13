// resources/js/services/academicYear.js
import axios from 'axios';

const ACADEMIC_YEARS_ENDPOINT = '/api/academic-years';

/**
 * Načíta zoznam akademických rokov / semestrov.
 * Backend: AcademicYearController@index
 *
 * @returns {Promise<Array>}
 */
export async function fetchAcademicYears() {
    const { data } = await axios.get(ACADEMIC_YEARS_ENDPOINT);

    if (Array.isArray(data)) return data;
    if (Array.isArray(data.data)) return data.data;
    return [];
}
