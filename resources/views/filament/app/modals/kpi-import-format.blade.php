<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800/50 dark:to-slate-700/50 p-6 rounded-lg border border-blue-200/50 dark:border-slate-600/50">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-blue-500 dark:bg-blue-600/80 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-50">Excel Import Format Guide</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Follow this format to successfully import KPI data</p>
            </div>
        </div>
    </div>

    {{-- Format Preview Table --}}
    <div class="bg-white dark:bg-slate-800/80 border border-gray-200/60 dark:border-slate-600/50 rounded-lg overflow-hidden shadow-sm">
        <div class="bg-gray-50/80 dark:bg-slate-700/60 px-6 py-4 border-b border-gray-200/60 dark:border-slate-600/50">
            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-50 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Correct Excel Format
            </h4>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200/60 dark:divide-slate-600/50">
                <thead class="bg-gray-800/95 dark:bg-slate-900/80">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-100 dark:text-gray-200 uppercase tracking-wider">
                            Column A - KPI Metric
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-100 dark:text-gray-200 uppercase tracking-wider">
                            Column B - Value
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-100 dark:text-gray-200 uppercase tracking-wider">
                            Column C - Notes
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200/60 dark:divide-slate-600/50">
                    <tr class="bg-yellow-50/80 dark:bg-yellow-900/15">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-50">KPI Metric</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-50">Value</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-50">Notes</td>
                    </tr>
                    <tr class="bg-white dark:bg-slate-800/40 hover:bg-gray-50/80 dark:hover:bg-slate-700/40">
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">Outbound Calls</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">75</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">Target achievement 75%</td>
                    </tr>
                    <tr class="bg-gray-50/60 dark:bg-slate-900/40 hover:bg-gray-100/80 dark:hover:bg-slate-700/40">
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">Talk Time</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">85</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">Increased from last month</td>
                    </tr>
                    <tr class="bg-white dark:bg-slate-800/40 hover:bg-gray-50/80 dark:hover:bg-slate-700/40">
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">Auto Quotes</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">92</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">Target achieved</td>
                    </tr>
                    <tr class="bg-gray-50/60 dark:bg-slate-900/40 hover:bg-gray-100/80 dark:hover:bg-slate-700/40">
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">Customer Satisfaction</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200">88</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">Needs improvement</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Rules Section --}}
    <div class="grid md:grid-cols-2 gap-6">
        {{-- Do's --}}
        <div class="bg-green-50/80 dark:bg-green-900/15 p-6 rounded-lg border border-green-200/60 dark:border-green-800/40">
            <h4 class="flex items-center text-green-800 dark:text-green-300 font-semibold mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Requirements
            </h4>
            <ul class="space-y-3 text-sm text-green-700 dark:text-green-300">
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-green-500 dark:bg-green-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>First row is header (will be skipped automatically)</span>
                </li>
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-green-500 dark:bg-green-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>KPI Metric name must match <strong>exactly</strong> with database data</span>
                </li>
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-green-500 dark:bg-green-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>Values in column B must be numbers</span>
                </li>
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-green-500 dark:bg-green-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>Save file in .xlsx format</span>
                </li>
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-green-500 dark:bg-green-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>Notes in column C are optional</span>
                </li>
            </ul>
        </div>

        {{-- Don'ts --}}
        <div class="bg-red-50/80 dark:bg-red-900/15 p-6 rounded-lg border border-red-200/60 dark:border-red-800/40">
            <h4 class="flex items-center text-red-800 dark:text-red-300 font-semibold mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                Common Mistakes
            </h4>
            <ul class="space-y-3 text-sm text-red-700 dark:text-red-300">
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-red-500 dark:bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>Adding extra spaces in KPI names</span>
                </li>
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-red-500 dark:bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>Filling Value column with text</span>
                </li>
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-red-500 dark:bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>Leaving Value column empty</span>
                </li>
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-red-500 dark:bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>Using file formats other than .xlsx</span>
                </li>
                <li class="flex items-start">
                    <span class="w-2 h-2 bg-red-500 dark:bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                    <span>Deleting or changing column order</span>
                </li>
            </ul>
        </div>
    </div>

    {{-- Available KPI Metrics --}}
    <div class="bg-blue-50/80 dark:bg-blue-900/15 p-6 rounded-lg border border-blue-200/60 dark:border-blue-800/40">
        <h4 class="flex items-center text-blue-800 dark:text-blue-300 font-semibold mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Available KPI Metrics in Database
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach(\App\Models\KpiMetric::where('is_active', true)->orderBy('name')->get() as $metric)
                <div class="bg-white/80 dark:bg-slate-800/60 p-4 rounded-lg border border-blue-200/50 dark:border-blue-700/40 hover:shadow-md hover:bg-white dark:hover:bg-slate-800/80 transition-all duration-200">
                    <div class="flex items-center mb-2">
                        <svg class="w-4 h-4 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="font-medium text-gray-900 dark:text-gray-200">{{ $metric->name }}</span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Target: {{ $metric->target_value }} {{ $metric->unit }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 p-4 bg-blue-100/60 dark:bg-blue-900/20 rounded-lg border border-blue-300/50 dark:border-blue-700/30">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-1">Important Note:</p>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Make sure the KPI Metric names in your Excel file match exactly with those in this list.
                        Case sensitivity and spacing matter.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Process Information --}}
    <div class="bg-yellow-50/80 dark:bg-yellow-900/15 p-5 rounded-lg border border-yellow-200/60 dark:border-yellow-800/40">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.748.457 1.7.157 2.126-.574z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <div>
                <h5 class="font-semibold text-yellow-800 dark:text-yellow-300 mb-2">Import Process</h5>
                <p class="text-sm text-yellow-700 dark:text-yellow-300 leading-relaxed">
                    The system will automatically validate each row, check for data integrity, and provide a detailed
                    report showing successfully imported data and any rows that were skipped due to validation errors.
                    Maximum file size is 5MB.
                </p>
            </div>
        </div>
    </div>

    {{-- Pro Tips --}}
    <div class="bg-gray-50/80 dark:bg-slate-800/60 p-5 rounded-lg border border-gray-200/60 dark:border-slate-700/50">
        <h5 class="font-semibold text-gray-800 dark:text-gray-200 mb-3 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Pro Tips
        </h5>
        <div class="space-y-3">
            <div class="flex items-start">
                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-500 dark:bg-blue-600/80 text-white text-xs font-medium rounded-full mr-3 mt-0.5 flex-shrink-0">1</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Use the "Download Template" button to get a pre-formatted Excel file</span>
            </div>
            <div class="flex items-start">
                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-500 dark:bg-blue-600/80 text-white text-xs font-medium rounded-full mr-3 mt-0.5 flex-shrink-0">2</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Double-check metric names for typos before importing</span>
            </div>
            <div class="flex items-start">
                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-500 dark:bg-blue-600/80 text-white text-xs font-medium rounded-full mr-3 mt-0.5 flex-shrink-0">3</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Keep file size under 5MB for optimal performance</span>
            </div>
            <div class="flex items-start">
                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-500 dark:bg-blue-600/80 text-white text-xs font-medium rounded-full mr-3 mt-0.5 flex-shrink-0">4</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Test with a small sample first before importing large datasets</span>
            </div>
        </div>
    </div>
    </div>
</div>