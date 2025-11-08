<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1 class="mb-0">Clients -> {{ client.name }}</h1>
            <a :href="`/clients/${client.id}/edit`" class="btn btn-primary">Edit Client</a>
        </div>

        <div class="flex">
            <div class="w-1/3 mr-5">
                <div class="w-full bg-white rounded p-4">
                    <h2>Client Info</h2>
                    <table>
                        <tbody>
                            <tr>
                                <th class="text-gray-600 pr-3">Name</th>
                                <td>{{ client.name }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-600 pr-3">Email</th>
                                <td>{{ client.email }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-600 pr-3">Phone</th>
                                <td>{{ client.phone }}</td>
                            </tr>
                            <tr>
                                <th class="text-gray-600 pr-3">Address</th>
                                <td>{{ client.address }}<br/>{{ client.postcode + ' ' + client.city }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="w-2/3">
                <div>
                    <button class="btn" :class="{'btn-primary': currentTab == 'bookings', 'btn-default': currentTab != 'bookings'}" @click="switchTab('bookings')">Bookings</button>
                    <button class="btn" :class="{'btn-primary': currentTab == 'journals', 'btn-default': currentTab != 'journals'}" @click="switchTab('journals')">Journals</button>
                </div>

                <!-- Bookings -->
                <div class="bg-white rounded p-4" v-if="currentTab == 'bookings'">
                    <h3 class="mb-3">List of client bookings</h3>

                    <!-- Filter Buttons -->
                    <div class="mb-3" v-if="client.bookings && client.bookings.length > 0">
                        <div class="btn-group" role="group">
                            <button
                                type="button"
                                class="btn btn-sm"
                                :class="bookingFilter === 'all' ? 'btn-primary' : 'btn-outline-primary'"
                                @click="setBookingFilter('all')"
                            >
                                All
                                <span class="badge badge-light ml-1">{{ bookingCounts.allCount }}</span>
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm"
                                :class="bookingFilter === 'upcoming' ? 'btn-primary' : 'btn-outline-primary'"
                                @click="setBookingFilter('upcoming')"
                            >
                                Upcoming
                                <span class="badge badge-light ml-1">{{ bookingCounts.upcomingCount }}</span>
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm"
                                :class="bookingFilter === 'past' ? 'btn-primary' : 'btn-outline-primary'"
                                @click="setBookingFilter('past')"
                            >
                                Past
                                <span class="badge badge-light ml-1">{{ bookingCounts.pastCount }}</span>
                            </button>
                        </div>
                    </div>

                    <template v-if="client.bookings && client.bookings.length > 0">
                        <table>
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="booking in filteredBookings" :key="booking.id">
                                    <td>{{ booking.start }} - {{ booking.end }}</td>
                                    <td>{{ booking.notes }}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" @click="deleteBooking(booking)">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p v-if="filteredBookings.length === 0" class="text-center text-muted mt-3">
                            No {{ bookingFilter }} bookings found.
                        </p>
                    </template>

                    <template v-else>
                        <p class="text-center">The client has no bookings.</p>
                    </template>

                </div>

                <!-- Journals -->
                <div class="bg-white rounded p-4" v-if="currentTab == 'journals'">
                    <h3 class="mb-3">List of client journals</h3>

                    <p>(BONUS) TODO: implement this feature</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ClientShow',

    props: ['client'],

    data() {
        return {
            currentTab: 'bookings',
            bookingFilter: 'upcoming',
        }
    },

    computed: {
        filteredBookings() {
            if (!this.client.bookings || this.client.bookings.length === 0) {
                return [];
            }

            const now = new Date();
            let filtered = [];

            if (this.bookingFilter === 'upcoming') {
                filtered = this.client.bookings.filter(booking => new Date(booking.start) >= now);
                // Sort ascending (soonest first)
                filtered.sort((a, b) => new Date(a.start) - new Date(b.start));
            } else if (this.bookingFilter === 'past') {
                filtered = this.client.bookings.filter(booking => new Date(booking.start) < now);
                // Sort descending (most recent first)
                filtered.sort((a, b) => new Date(b.start) - new Date(a.start));
            } else {
                // All bookings
                filtered = [...this.client.bookings];
                // Sort ascending
                filtered.sort((a, b) => new Date(a.start) - new Date(b.start));
            }

            return filtered;
        },

        bookingCounts() {
            if (!this.client.bookings || this.client.bookings.length === 0) {
                return {
                    allCount: 0,
                    upcomingCount: 0,
                    pastCount: 0
                };
            }

            const now = new Date();
            const upcoming = this.client.bookings.filter(booking => new Date(booking.start) >= now);
            const past = this.client.bookings.filter(booking => new Date(booking.start) < now);

            return {
                allCount: this.client.bookings.length,
                upcomingCount: upcoming.length,
                pastCount: past.length
            };
        }
    },

    methods: {
        switchTab(newTab) {
            this.currentTab = newTab;
        },

        setBookingFilter(filter) {
            this.bookingFilter = filter;
        },

        deleteBooking(booking) {
            axios.delete(`/bookings/${booking.id}`);
        }
    }
}
</script>
