<?php
session_start();
if(isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    header("Location: ../../auth/login1.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Absence Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .absence-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .chart-container {
            height: 300px;
        }
        /* Custom scrollbar */
        .history-container::-webkit-scrollbar {
            width: 8px;
        }
        .history-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .history-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .history-container::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex justify-end">
        <a href="../../auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
    </div>
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-indigo-600 text-white shadow-md">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-user-graduate text-2xl"></i>
                    <h1 class="text-2xl font-bold">STUDENT ABSENCE DASHBOARD</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button class="p-2 rounded-full bg-indigo-700 hover:bg-indigo-800">
                            <i class="fas fa-bell"></i>
                            <span class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full"></span>
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <img src="https://placehold.co/40x40" alt="Profile picture of current user" class="w-8 h-8 rounded-full">
                        <span>Admin</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow container mx-auto px-4 py-6">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Left Column (Stats and Actions) -->
                <div class="lg:w-1/3 space-y-6">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
                        <!-- Today's Absences -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500">Today's Absences</p>
                                    <p class="text-3xl font-bold">8</p>
                                </div>
                                <div class="bg-blue-100 p-3 rounded-full">
                                    <i class="fas fa-calendar-day text-blue-500 text-xl"></i>
                                </div>
                            </div>
                            <div class="mt-2 text-sm text-gray-500">
                                <span class="text-green-500">↓ 2%</span> from yesterday
                            </div>
                        </div>

                        <!-- Monthly Summary -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500">This Month</p>
                                    <p class="text-3xl font-bold">32</p>
                                </div>
                                <div class="bg-red-100 p-3 rounded-full">
                                    <i class="fas fa-calendar-alt text-red-500 text-xl"></i>
                                </div>
                            </div>
                            <div class="mt-2 text-sm text-gray-500">
                                <span class="text-green-500">↓ 15%</span> from last month
                            </div>
                        </div>

                        <!-- Reason Distribution -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500 lg:col-span-1 md:col-span-2">
                            <h3 class="font-semibold mb-4">Absence Reasons</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Sick</span>
                                    <span class="font-semibold">56%</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-purple-500 rounded-full" style="width: 56%"></div>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span>Personal</span>
                                    <span class="font-semibold">24%</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" style="width: 24%"></div>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span>Other</span>
                                    <span class="font-semibold">20%</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-orange-500 rounded-full" style="width: 20%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="font-semibold mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md flex items-center justify-center space-x-2">
                                <i class="fas fa-plus"></i>
                                <span>Record New Absence</span>
                            </button>
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md flex items-center justify-center space-x-2">
                                <i class="fas fa-file-export"></i>
                                <span>Generate Report</span>
                            </button>
                            <button class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 px-4 rounded-md flex items-center justify-center space-x-2">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </button>
                        </div>
                    </div>

                    <!-- Recent Notifications -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h3 class="font-semibold mb-4">Recent Notifications</h3>
                        <div class="space-y-3">
                            <div class="flex items-start space-x-3">
                                <div class="bg-green-100 p-2 rounded-full">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Attendance report generated</p>
                                    <p class="text-sm text-gray-500">2 hours ago</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="bg-red-100 p-2 rounded-full">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div>
                                    <p class="font-medium">High absence rate detected</p>
                                    <p class="text-sm text-gray-500">Yesterday</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="bg-blue-100 p-2 rounded-full">
                                    <i class="fas fa-info-circle text-blue-500"></i>
                                </div>
                                <div>
                                    <p class="font-medium">System updated to v2.1</p>
                                    <p class="text-sm text-gray-500">3 days ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Graph and History) -->
                <div class="lg:w-2/3 space-y-6">
                    <!-- Attendance Chart -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold">Weekly Absence Trend</h3>
                            <select class="bg-gray-100 border border-gray-300 rounded-md px-3 py-1 text-sm">
                                <option>This Week</option>
                                <option>Last Week</option>
                                <option>This Month</option>
                            </select>
                        </div>
                        <div class="chart-container">
                            <!-- This would be replaced with an actual chart library in production -->
                            <img src="https://placehold.co/800x300" alt="Placeholder for attendance trend chart showing weekly absence data with peaks on Monday and Friday" class="w-full h-full object-contain bg-gray-50 rounded">
                        </div>
                        <div class="grid grid-cols-7 gap-2 mt-4 text-center text-xs">
                            <div>Mon</div>
                            <div>Tue</div>
                            <div>Wed</div>
                            <div>Thu</div>
                            <div>Fri</div>
                            <div>Sat</div>
                            <div>Sun</div>
                        </div>
                    </div>

                    <!-- Recent Absences -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold">Today's Absences</h3>
                            <button class="text-sm text-indigo-600 hover:text-indigo-800">View All</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img src="https://placehold.co/40x40" alt="Student portrait - Asian female with glasses and black hair" class="h-10 w-10 rounded-full">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Sarah Lim</div>
                                                    <div class="text-sm text-gray-500">Grade 11</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Mathematics</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">Sick</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Unexcused</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            08:15 AM
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img src="https://placehold.co/40x40" alt="Student portrait - Caucasian male with short brown hair" class="h-10 w-10 rounded-full">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Michael Chen</div>
                                                    <div class="text-sm text-gray-500">Grade 10</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">Science</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Medical</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Excused</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            09:30 AM
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img src="https://placehold.co/40x40" alt="Student portrait - African American female with curly hair" class="h-10 w-10 rounded-full">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Jamila Williams</div>
                                                    <div class="text-sm text-gray-500">Grade 12</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">History</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">Family</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            10:15 AM
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Absence History -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold">Recent Absence History</h3>
                            <button class="text-sm text-indigo-600 hover:text-indigo-800">View All</button>
                        </div>
                        <div class="history-container overflow-y-auto" style="max-height: 400px;">
                            <div class="space-y-4 pr-3">
                                <!-- History Item -->
                                <div class="absence-card p-4 border border-gray-200 rounded-lg transition-all duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex space-x-3">
                                            <img src="https://placehold.co/50x50" alt="Student portrait - Hispanic male with short black hair and smiling" class="w-12 h-12 rounded-full">
                                            <div>
                                                <h4 class="font-medium">Daniel Rodriguez</h4>
                                                <p class="text-sm text-gray-500">Grade 11 - Physics</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Sick</span>
                                            <p class="text-sm text-gray-500 mt-1">Oct 12, 2023</p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm">Note: Reported flu symptoms, doctor's note provided</p>
                                    </div>
                                </div>

                                <!-- History Item -->
                                <div class="absence-card p-4 border border-gray-200 rounded-lg transition-all duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex space-x-3">
                                            <img src="https://placehold.co/50x50" alt="Student portrait - Caucasian female with blonde hair tied in a ponytail" class="w-12 h-12 rounded-full">
                                            <div>
                                                <h4 class="font-medium">Emily Wilson</h4>
                                                <p class="text-sm text-gray-500">Grade 10 - Literature</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Medical</span>
                                            <p class="text-sm text-gray-500 mt-1">Oct 10, 2023</p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm">Note: Scheduled dentist appointment</p>
                                    </div>
                                </div>

                                <!-- History Item -->
                                <div class="absence-card p-4 border border-gray-200 rounded-lg transition-all duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex space-x-3">
                                            <img src="https://placehold.co/50x50" alt="Student portrait - South Asian male with short black hair and glasses" class="w-12 h-12 rounded-full">
                                            <div>
                                                <h4 class="font-medium">Arjun Patel</h4>
                                                <p class="text-sm text-gray-500">Grade 12 - Chemistry</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">Family</span>
                                            <p class="text-sm text-gray-500 mt-1">Oct 8, 2023</p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm">Note: Family emergency, provided documentation</p>
                                    </div>
                                </div>

                                <!-- History Item -->
                                <div class="absence-card p-4 border border-gray-200 rounded-lg transition-all duration-200">
                                    <div class="flex justify-between items-start">
                                        <div class="flex space-x-3">
                                            <img src="https://placehold.co/50x50" alt="Student portrait - Mixed race female with curly brown hair and freckles" class="w-12 h-12 rounded-full">
                                            <div>
                                                <h4 class="font-medium">Sophia Lee</h4>
                                                <p class="text-sm text-gray-500">Grade 9 - Geography</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full">Other</span>
                                            <p class="text-sm text-gray-500 mt-1">Oct 5, 2023</p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm">Note: Transportation issues, late arrival</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-500 text-sm">© 2023 Student Attendance System. All rights reserved.</p>
                    <div class="flex space-x-4 mt-3 md:mt-0">
                        <a href="#" class="text-gray-500 hover:text-gray-700">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-gray-700">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-gray-700">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Placeholder for any JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Tooltips for status badges
            const statusBadges = document.querySelectorAll('span.bg-red-100, span.bg-green-100, span.bg-yellow-100');
            statusBadges.forEach(badge => {
                badge.addEventListener('mouseover', function() {
                    // In a real implementation, you'd show a tooltip with more details
                    console.log(`Status: ${this.textContent.trim()}`);
                });
            });

            // In a real implementation, this would fetch data and render charts
            // Using Chart.js or similar library
            function renderChart() {
                console.log('Chart would be rendered here with attendance data');
            }

            renderChart();
        });
    </script>
</body>
</html>
