<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details - Elite Car Wash</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- html2pdf library for PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
                sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }
            .no-print {
                display: none !important;
            }
            #jobDetailPrint .print-header {
                background: linear-gradient(to right, #2563eb, #4f46e5) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
            }
            #jobDetailPrint .print-amount {
                background: linear-gradient(to bottom right, #3b82f6, #4f46e5) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
            }
            #jobDetailPrint .print-commission {
                background: linear-gradient(to bottom right, #10b981, #059669) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color: white !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div id="jobDetailPrint" class="min-h-screen bg-white">
        <!-- Header -->
        <div class="print-header bg-gradient-to-r from-blue-600 to-indigo-600 text-white p-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-black uppercase tracking-tighter mb-2">Job Details</h1>
                        <p class="text-sm opacity-90">Complete job information with inspections and expenses</p>
                        <p class="text-xs opacity-80 mt-2">
                            {{ $jobData['endTime'] ? \Carbon\Carbon::parse($jobData['endTime'])->format('l, F d, Y') : '' }}
                        </p>
                    </div>
                    <div class="no-print flex gap-3">
                        <a href="{{ route('car.wash.completed-jobs') }}" class="px-6 py-3 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm">
                            ← Back
                        </a>
                        <button onclick="window.print()" class="px-6 py-3 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm">
                            Print
                        </button>
                        <button onclick="downloadPDF()" class="px-6 py-3 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-black uppercase transition-colors backdrop-blur-sm">
                            Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto p-6">
            <div class="space-y-4">
                <!-- Customer & Vehicle Info -->
                <div class="print-section grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                        <p class="text-xs font-black text-slate-500 uppercase mb-2">Vehicle No</p>
                        <p class="text-lg font-black text-slate-900">{{ $jobData['vehicleNo'] ?? 'N/A' }}</p>
                    </div>
                    <div class="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                        <p class="text-xs font-black text-slate-500 uppercase mb-2">Customer</p>
                        <p class="text-lg font-black text-slate-900">{{ $jobData['customerName'] ?? 'N/A' }}</p>
                    </div>
                    <div class="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                        <p class="text-xs font-black text-slate-500 uppercase mb-2">Mobile</p>
                        <p class="text-lg font-black text-slate-900">{{ $jobData['mobile'] ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <!-- Service & Worker -->
                <div class="print-section grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                        <p class="text-xs font-black text-slate-500 uppercase mb-2">Service</p>
                        <p class="text-lg font-black text-slate-900">{{ $jobData['serviceName'] ?? 'N/A' }}</p>
                    </div>
                    <div class="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                        <p class="text-xs font-black text-slate-500 uppercase mb-2">Worker</p>
                        <p class="text-lg font-black text-slate-900">{{ $jobData['workerName'] ?? 'N/A' }}</p>
                    </div>
                </div>
                
                <!-- Time, Amount & Commission -->
                <div class="print-section grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                        <p class="text-xs font-black text-slate-500 uppercase mb-2">Start Time</p>
                        <p class="text-lg font-black text-slate-900">
                            {{ $jobData['startTime'] ? \Carbon\Carbon::parse($jobData['startTime'])->format('M d, Y h:i A') : 'N/A' }}
                        </p>
                    </div>
                    <div class="print-card bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                        <p class="text-xs font-black text-slate-500 uppercase mb-2">End Time</p>
                        <p class="text-lg font-black text-slate-900">
                            {{ $jobData['endTime'] ? \Carbon\Carbon::parse($jobData['endTime'])->format('M d, Y h:i A') : 'N/A' }}
                        </p>
                    </div>
                    <div class="print-amount print-card bg-gradient-to-br from-blue-500 to-indigo-600 p-4 rounded-xl border-2 border-blue-400">
                        <p class="text-xs font-black text-white/90 uppercase mb-2">Amount</p>
                        <p class="text-2xl font-black text-white">Rs.{{ number_format($jobData['price'] ?? 0, 0) }}</p>
                    </div>
                    <div class="print-commission print-card bg-gradient-to-br from-emerald-500 to-green-600 p-4 rounded-xl border-2 border-emerald-400">
                        <p class="text-xs font-black text-white/90 uppercase mb-2">Commission</p>
                        @if(($jobData['workerCommission'] ?? 0) > 0)
                            <div>
                                <p class="text-2xl font-black text-white font-mono">Rs.{{ number_format($jobData['commissionAmount'] ?? 0, 0) }}</p>
                                <p class="text-xs text-white/80 mt-1">({{ $jobData['workerCommission'] }}%)</p>
                            </div>
                        @else
                            <p class="text-lg font-black text-white/70">-</p>
                        @endif
                    </div>
                </div>
                
                <!-- Inspection Details -->
                @if(isset($jobData['inspection']) && isset($jobData['inspection']['inspectionItems']) && !empty($jobData['inspection']['inspectionItems']))
                <div class="print-section bg-purple-50 p-6 rounded-xl border-2 border-purple-200">
                    <h3 class="text-lg font-black text-purple-900 uppercase mb-4">Inspection Details</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $itemNames = [
                                'engine_oil' => 'Engine Oil',
                                'gear_oil' => 'Gear Oil',
                                'brake_oil' => 'Brake Oil',
                                'air_filter' => 'Air Filter',
                                'radiator_water' => 'Radiator Water',
                                'shower_water' => 'Shower Water',
                                'power_oil' => 'Power Oil',
                                'horn' => 'Horn',
                                'head_lights' => 'Head Lights',
                                'indicator' => 'Indicator',
                                'brake_pad' => 'Brake Pad',
                                'ac_filter' => 'AC Filter'
                            ];
                            $statusIcons = [
                                'excellent' => '⭐',
                                'good' => '✅',
                                'average' => '⚠️',
                                'poor' => '❌'
                            ];
                        @endphp
                        @foreach($jobData['inspection']['inspectionItems'] as $itemId => $item)
                            @if(is_array($item) && isset($item['status']))
                                <div class="bg-white p-3 rounded-lg border border-purple-200">
                                    <p class="text-xs font-black text-slate-600 uppercase mb-1">{{ $itemNames[$itemId] ?? $itemId }}</p>
                                    <p class="text-sm font-black text-purple-700">{{ $statusIcons[$item['status'] ?? ''] ?? '⚪' }} {{ ucfirst($item['status'] ?? 'N/A') }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- Expense Details -->
                @if(isset($jobData['expense']) && isset($jobData['expense']['expenseItems']) && is_array($jobData['expense']['expenseItems']) && count($jobData['expense']['expenseItems']) > 0)
                <div class="print-section bg-orange-50 p-6 rounded-xl border-2 border-orange-200">
                    <h3 class="text-lg font-black text-orange-900 uppercase mb-4">Expense Details</h3>
                    <div class="space-y-2">
                        @foreach($jobData['expense']['expenseItems'] as $item)
                            @if(is_array($item) && isset($item['name']))
                                <div class="bg-white p-3 rounded-lg border border-orange-200 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-black text-slate-900">{{ $item['name'] ?? 'N/A' }}</p>
                                        <p class="text-xs text-slate-500">Qty: {{ $item['quantity'] ?? 0 }} × Rs.{{ number_format($item['price'] ?? 0, 0) }}</p>
                                    </div>
                                    <p class="text-sm font-black text-orange-600">Rs.{{ number_format($item['total'] ?? (($item['quantity'] ?? 0) * ($item['price'] ?? 0)), 0) }}</p>
                                </div>
                            @endif
                        @endforeach
                        <div class="bg-orange-200 p-3 rounded-lg border-2 border-orange-300 flex justify-between items-center mt-4">
                            <p class="text-sm font-black text-orange-900 uppercase">Total Expense</p>
                            <p class="text-lg font-black text-orange-900">Rs.{{ number_format($jobData['expense']['totalAmount'] ?? 0, 0) }}</p>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Summary Section -->
                <div class="print-section bg-gradient-to-br from-slate-100 to-slate-200 p-6 rounded-xl border-2 border-slate-300">
                    <h3 class="text-lg font-black text-slate-900 uppercase mb-4">Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center bg-white p-3 rounded-lg">
                            <p class="text-sm font-black text-slate-700">Total Amount:</p>
                            <p class="text-lg font-black text-blue-600 font-mono">Rs.{{ number_format($jobData['price'] ?? 0, 0) }}</p>
                        </div>
                        @if(($jobData['workerCommission'] ?? 0) > 0)
                            <div class="flex justify-between items-center bg-white p-3 rounded-lg">
                                <p class="text-sm font-black text-slate-700">Worker Commission ({{ $jobData['workerCommission'] }}%):</p>
                                <p class="text-lg font-black text-emerald-600 font-mono">Rs.{{ number_format($jobData['commissionAmount'] ?? 0, 0) }}</p>
                            </div>
                        @endif
                        @if(isset($jobData['expense']['totalAmount']) && $jobData['expense']['totalAmount'] > 0)
                            <div class="flex justify-between items-center bg-white p-3 rounded-lg">
                                <p class="text-sm font-black text-slate-700">Total Expenses:</p>
                                <p class="text-lg font-black text-orange-600 font-mono">Rs.{{ number_format($jobData['expense']['totalAmount'], 0) }}</p>
                            </div>
                        @endif
                        <div class="flex justify-between items-center bg-gradient-to-r from-blue-500 to-indigo-600 p-4 rounded-lg mt-4">
                            <p class="text-base font-black text-white uppercase">Net Amount:</p>
                            <p class="text-xl font-black text-white font-mono">
                                Rs.{{ number_format(($jobData['price'] ?? 0) - ($jobData['commissionAmount'] ?? 0) - ($jobData['expense']['totalAmount'] ?? 0), 0) }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Notes/Comments -->
                @if(!empty($jobData['notes']))
                <div class="print-section bg-slate-50 p-4 rounded-xl border-2 border-slate-200">
                    <p class="text-xs font-black text-slate-500 uppercase mb-2">Notes</p>
                    <p class="text-sm text-slate-900">{{ $jobData['notes'] }}</p>
                </div>
                @endif
            </div>
        </main>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('jobDetailPrint');
            const opt = {
                margin: [10, 10, 10, 10],
                filename: 'job-{{ $jobData["id"] ?? "detail" }}-{{ \Carbon\Carbon::now()->format("Y-m-d") }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
