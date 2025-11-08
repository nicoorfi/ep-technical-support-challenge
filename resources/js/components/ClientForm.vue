<template>
    <div>
        <h1 class="mb-6">Clients -> {{ isEdit ? 'Edit Client' : 'Add New Client' }}</h1>

        <!-- Error Notification -->
        <div v-if="showError" class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>Error!</strong> {{ errorMessage }}
            <button type="button" class="close" @click="showError = false">
                <span>&times;</span>
            </button>
        </div>

        <div class="max-w-lg mx-auto">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" class="form-control" v-model="formData.name">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" class="form-control" v-model="formData.email">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" class="form-control" v-model="formData.phone">
            </div>
            <div class="form-group">
                <label for="name">Address</label>
                <input type="text" id="address" class="form-control" v-model="formData.address">
            </div>
            <div class="flex">
                <div class="form-group flex-1">
                    <label for="city">City</label>
                    <input type="text" id="city" class="form-control" v-model="formData.city">
                </div>
                <div class="form-group flex-1">
                    <label for="postcode">Postcode</label>
                    <input type="text" id="postcode" class="form-control" v-model="formData.postcode">
                </div>
            </div>

            <div class="text-right">
                <a href="/clients" class="btn btn-default">Cancel</a>
                <button
                    @click="submitForm"
                    class="btn btn-primary"
                    :disabled="submitting"
                >
                    <span v-if="submitting">
                        <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                        {{ isEdit ? 'Updating...' : 'Creating...' }}
                    </span>
                    <span v-else>{{ isEdit ? 'Update' : 'Create' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ClientForm',

    props: {
        client: {
            type: Object,
            default: null
        },
        isEdit: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            formData: {
                name: this.client?.name || '',
                email: this.client?.email || '',
                phone: this.client?.phone || '',
                address: this.client?.address || '',
                city: this.client?.city || '',
                postcode: this.client?.postcode || '',
            },
            submitting: false,
            showError: false,
            errorMessage: ''
        }
    },

    methods: {
        async submitForm() {
            if (this.submitting) {
                return;
            }

            this.submitting = true;
            this.showError = false;

            try {
                let response;

                if (this.isEdit) {
                    response = await axios.put(`/clients/${this.client.id}`, this.formData);
                } else {
                    response = await axios.post('/clients', this.formData);
                }

                window.location.href = response.data.url;
            } catch (error) {
                this.submitting = false;

                if (error.response && error.response.status === 422) {
                    this.errorMessage = 'Please check your input and try again.';
                } else if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
                    this.errorMessage = 'Request timed out. Please check your connection and try again.';
                } else {
                    const action = this.isEdit ? 'update' : 'create';
                    this.errorMessage = `Failed to ${action} client. Please try again.`;
                }

                this.showError = true;

                setTimeout(() => {
                    this.showError = false;
                }, 5000);
            }
        }
    }
}
</script>

<style scoped>
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
