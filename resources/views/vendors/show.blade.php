<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Vendor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Vendor Details</h2>
                <a href="{{ route('vendors.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>

            <!-- Vendor Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Name</h3>
                    <p class="text-gray-900">{{ $vendor->name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Email</h3>
                    <p class="text-gray-900">{{ $vendor->email ?? 'N/A' }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Phone</h3>
                    <p class="text-gray-900">{{ $vendor->phone ?? 'N/A' }}</p>
                </div>

                <!-- Tax Number -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Tax Number</h3>
                    <p class="text-gray-900">{{ $vendor->tax_number ?? 'N/A' }}</p>
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Address</h3>
                    <p class="text-gray-900">{{ $vendor->address ?? 'N/A' }}</p>
                </div>

                <!-- Status -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Status</h3>
                    <p class="text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $vendor->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Vendor Statistics -->
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Total Bills</h4>
                        <p class="text-gray-900">{{ $statistics['total_bills'] }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Total Amount</h4>
                        <p class="text-gray-900">${{ number_format($statistics['total_amount'], 2) }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Total Paid</h4>
                        <p class="text-gray-900">${{ number_format($statistics['total_paid'], 2) }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Total Due</h4>
                        ${{ number_format($statistics['total_due'], 2) }}
