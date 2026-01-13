// resources/js/services/company.js
import axios from 'axios';

const COMPANY_SEARCH_ENDPOINT = '/api/companies/search';
const COMPANY_DETAIL_ENDPOINT = '/api/companies';

/**
 * Vyhľadá firmy podľa názvu.
 * Backend: CompanyController@searchByName
 * - GET /api/companies/search?q=...
 * - response: [{ id, name }]
 *
 * @param {string} query
 * @returns {Promise<Array<{id:number, name:string}>>}
 */
export async function searchCompaniesByName(query) {
    const { data } = await axios.get(COMPANY_SEARCH_ENDPOINT, {
        params: { q: query },
    });

    // backend vracia priamo pole firiem
    return data;
}

/**
 * Načíta detail firmy podľa ID.
 * Backend: CompanyController@show
 * - GET /api/companies/{id}
 *
 * @param {number} id
 * @returns {Promise<object>}
 */
export async function fetchCompanyDetail(id) {
    const { data } = await axios.get(`${COMPANY_DETAIL_ENDPOINT}/${id}`);
    return data;
}
