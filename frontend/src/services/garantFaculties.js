import axios from 'axios';

export async function fetchMyFaculties() {
    const { data } = await axios.get('/api/garant/my-faculties');
    return data.faculties ?? [];
}

export async function saveMyFaculties(facultyIds) {
    const { data } = await axios.post('/api/garant/save-my-faculties', {
        faculty_ids: facultyIds,
    });
    return data;
}
