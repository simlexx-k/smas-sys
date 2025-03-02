<template>
  <div>
    <h1>Exams</h1>
    <button @click="showCreateModal = true">Create Exam</button>

    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Date</th>
          <th>Description</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="exam in exams" :key="exam.id">
          <td>{{ exam.name }}</td>
          <td>{{ formatDate(exam.date) }}</td>
          <td>{{ exam.description }}</td>
          <td>
            <button @click="editExam(exam)">Edit</button>
            <button @click="deleteExam(exam.id)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal">
      <h2>{{ isEditing ? 'Edit' : 'Create' }} Exam</h2>
      <form @submit.prevent="isEditing ? updateExam() : createExam()">
        <div>
          <label>Name:</label>
          <input v-model="currentExam.name" required />
        </div>
        <div>
          <label>Date:</label>
          <input type="date" v-model="currentExam.date" required />
        </div>
        <div>
          <label>Description:</label>
          <textarea v-model="currentExam.description"></textarea>
        </div>
        <button type="submit">{{ isEditing ? 'Update' : 'Create' }}</button>
        <button type="button" @click="closeModal">Cancel</button>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      exams: [],
      showCreateModal: false,
      showEditModal: false,
      isEditing: false,
      currentExam: {
        id: null,
        name: '',
        date: '',
        description: ''
      }
    };
  },
  async created() {
    await this.fetchExams();
  },
  methods: {
    async fetchExams() {
      const response = await axios.get('/api/exams');
      this.exams = response.data;
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString();
    },
    openCreateModal() {
      this.currentExam = { id: null, name: '', date: '', description: '' };
      this.showCreateModal = true;
      this.isEditing = false;
    },
    editExam(exam) {
      this.currentExam = { ...exam };
      this.showEditModal = true;
      this.isEditing = true;
    },
    async createExam() {
      await axios.post('/api/exams', this.currentExam);
      this.closeModal();
      await this.fetchExams();
    },
    async updateExam() {
      await axios.put(`/api/exams/${this.currentExam.id}`, this.currentExam);
      this.closeModal();
      await this.fetchExams();
    },
    async deleteExam(id) {
      await axios.delete(`/api/exams/${id}`);
      await this.fetchExams();
    },
    closeModal() {
      this.showCreateModal = false;
      this.showEditModal = false;
    }
  }
};
</script>

<style scoped>
/* Add your styles here */
</style>
