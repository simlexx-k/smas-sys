<template>
  <div>
    <h1>Students</h1>
    <button @click="openCreateModal" class="btn btn-primary mb-3">Add Student</button>

    <div v-if="loading" class="spinner-border" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>

    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <table class="table" v-if="!loading">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.id">
          <td>{{ student.name }}</td>
          <td>{{ student.email }}</td>
          <td>{{ student.phone }}</td>
          <td>
            <button @click="openEditModal(student)" class="btn btn-sm btn-primary">Edit</button>
            <button @click="deleteStudent(student.id)" class="btn btn-sm btn-danger">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Create/Edit Modal -->
    <div class="modal" :class="{ 'show': showModal }">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit Student' : 'Add Student' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveStudent">
              <div class="mb-3">
                <label class="form-label">Name</label>
                <input v-model="form.name" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Phone</label>
                <input v-model="form.phone" type="text" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary">Save</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

interface Student {
  id: number;
  name: string;
  email: string;
  phone: string;
}

const students = ref<Student[]>([]);
const showModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const error = ref<string | null>(null);
const form = ref({
  id: null,
  name: '',
  email: '',
  phone: ''
});

const fetchStudents = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await axios.get('/api/students');
    students.value = response.data;
  } catch (err) {
    error.value = 'Failed to fetch students';
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  form.value = { id: null, name: '', email: '', phone: '' };
  isEditing.value = false;
  showModal.value = true;
};

const openEditModal = (student: Student) => {
  form.value = { ...student };
  isEditing.value = true;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveStudent = async () => {
  try {
    if (isEditing.value) {
      await axios.put(`/api/students/${form.value.id}`, form.value);
    } else {
      await axios.post('/api/students', form.value);
    }
    await fetchStudents();
    closeModal();
  } catch (err) {
    error.value = 'Failed to save student';
  }
};

const deleteStudent = async (id: number) => {
  try {
    await axios.delete(`/api/students/${id}`);
    await fetchStudents();
  } catch (err) {
    error.value = 'Failed to delete student';
  }
};

onMounted(() => {
  fetchStudents();
});
</script>

<style scoped>
.modal {
  display: none;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
  display: block;
}
</style>
