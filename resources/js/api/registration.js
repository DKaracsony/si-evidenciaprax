import axios from 'axios';

export async function getFaculties() {
    const { data } = await axios.get('/api/faculties');
    return data; // expect array of { id, name, ... }
}

export async function postStudent(payload) {
    // payload must include form_type: 'student_form'
    const { data, status } = await axios.post('/api/register', payload);
    return { data, status };
}
