<template>
    <div>
        <h1>
            Clients
            <a href="/clients/create" class="float-right btn btn-primary">+ New Client</a>
        </h1>

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
                <tr v-for="client in filteredClients" :key="client.id">
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
                        <button class="btn btn-danger btn-sm" @click="deleteClient(client)">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
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
            loading: false
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

        deleteClient(client) {
            axios.delete(`/clients/${client.id}`);
        }
    }
}
</script>
