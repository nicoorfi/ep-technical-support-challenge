<template>
    <div>
        <h1>
            Clients
            <a href="/clients/create" class="float-right btn btn-primary">+ New Client</a>
        </h1>

        <!-- Success Notification -->
        <div v-if="showNotification" class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>Success!</strong> {{ notificationMessage }}
            <button type="button" class="close" @click="showNotification = false">
                <span>&times;</span>
            </button>
        </div>

        <!-- Error Notification -->
        <div v-if="showError" class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>Error!</strong> {{ errorMessage }}
            <button type="button" class="close" @click="showError = false">
                <span>&times;</span>
            </button>
        </div>

        <div class="form-group">
            <input
                type="text"
                class="form-control"
                placeholder="Search clients by name, email, or phone..."
                v-model="searchTerm"
            />
        </div>

        <div v-if="loading" class="text-center my-3">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <table class="table" v-else>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Number of Bookings</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="client in filteredClients" :key="client.id" :class="{ 'deleting': deletingClientId === client.id }">
                    <td>
                        {{ client.name }}
                        <span
                            v-if="client.user && currentUser && client.user.id !== currentUser.id"
                            class="badge badge-info text-white ml-2"
                        >
                            assigned by {{ client.user.name }}
                        </span>
                    </td>
                    <td>{{ client.email }}</td>
                    <td>{{ client.phone }}</td>
                    <td>{{ client.bookings_count }}</td>
                    <td>
                        <a class="btn btn-primary btn-sm" :href="`/clients/${client.id}`">View</a>
                        <button
                            v-if="client.can_be_deleted"
                            class="btn btn-danger btn-sm"
                            @click="confirmDelete(client)"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" :class="{ 'show d-block': showDeleteModal }" tabindex="-1" role="dialog" v-if="showDeleteModal" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="close" @click="showDeleteModal = false">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong>{{ clientToDelete && clientToDelete.name }}</strong>?</p>
                        <p class="text-muted">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="deleteClient">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ClientsList',

    props: ['clients'],

    data() {
        return {
            searchTerm: '',
            filteredClients: this.clients,
            debounceTimeout: null,
            loading: false,
            showDeleteModal: false,
            clientToDelete: null,
            deletingClientId: null,
            showNotification: false,
            notificationMessage: '',
            showError: false,
            errorMessage: ''
        };
    },

    watch: {
        searchTerm(newValue) {
            clearTimeout(this.debounceTimeout);

            this.debounceTimeout = setTimeout(() => {
                this.fetchClients();
            }, 300);
        }
    },

    methods: {
        async fetchClients() {
            this.loading = true;

            try {
                const response = await axios.get('/clients', {
                    params: { search: this.searchTerm },
                    headers: { 'Accept': 'application/json' }
                });
                this.filteredClients = response.data;
            } catch (error) {
                console.error('Error fetching clients:', error);
            } finally {
                this.loading = false;
            }
        },

        confirmDelete(client) {
            this.clientToDelete = client;
            this.showDeleteModal = true;
        },

        async deleteClient() {
            if (!this.clientToDelete) return;

            const clientToDelete = this.clientToDelete;
            this.showDeleteModal = false;
            this.deletingClientId = clientToDelete.id;

            try {
                await axios.delete(`/clients/${clientToDelete.id}`);

                // Wait for animation before removing from array
                setTimeout(() => {
                    this.filteredClients = this.filteredClients.filter(
                        c => c.id !== clientToDelete.id
                    );
                    this.deletingClientId = null;
                }, 500);

                // Show success notification
                this.notificationMessage = `Client "${clientToDelete.name}" has been successfully deleted.`;
                this.showNotification = true;

                // Auto-hide notification after 4 seconds
                setTimeout(() => {
                    this.showNotification = false;
                }, 4000);

                this.clientToDelete = null;
            } catch (error) {
                this.deletingClientId = null;

                // Show appropriate error message
                if (error.response && error.response.status === 403) {
                    this.errorMessage = 'You do not have permission to delete this client.';
                } else {
                    this.errorMessage = 'Failed to delete client. Please try again.';
                }

                this.showError = true;

                // Auto-hide error after 4 seconds
                setTimeout(() => {
                    this.showError = false;
                }, 4000);

                this.clientToDelete = null;
                console.error('Error deleting client:', error);
            }
        }
    }
}
</script>

<style scoped>
.deleting {
    background-color: #ffe6e6 !important;
    animation: fadeOut 0.5s ease-out;
}

@keyframes fadeOut {
    0% {
        opacity: 1;
        transform: translateX(0);
    }
    100% {
        opacity: 0;
        transform: translateX(-20px);
    }
}

.modal.show {
    display: block;
}

.alert {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
