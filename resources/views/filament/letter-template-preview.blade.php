{{-- resources/views/filament/letter-template-preview.blade.php --}}

<div class="space-y-6">
    {{-- Template Header --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ $record->name }}
            </h3>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                {{ ucfirst($record->category) }}
            </span>
        </div>
        
        @if($record->description)
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                {{ $record->description }}
            </p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700 dark:text-gray-300">Required Fields:</span>
                <div class="mt-1 flex flex-wrap gap-1">
                    @foreach($record->required_fields ?? [] as $field)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            {{ $field }}
                        </span>
                    @endforeach
                </div>
            </div>
            
            @if($record->optional_fields && count($record->optional_fields) > 0)
                <div>
                    <span class="font-medium text-gray-700 dark:text-gray-300">Optional Fields:</span>
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach($record->optional_fields as $field)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ $field }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- AI Instructions --}}
    @if($record->ai_instructions)
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-800">
            <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-200 mb-2 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                AI Instructions
            </h4>
            <p class="text-sm text-amber-700 dark:text-amber-300">
                {{ $record->ai_instructions }}
            </p>
        </div>
    @endif

    {{-- Template Content Preview --}}
    <div class="space-y-4">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">
            Template Content
        </h4>
        
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-h-96 overflow-y-auto">
            <div class="font-mono text-sm whitespace-pre-wrap text-gray-800 dark:text-gray-200 leading-relaxed">{{ $record->template_content }}</div>
        </div>
    </div>

    {{-- Placeholder Explanation --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
        <h5 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            Template Variables
        </h5>
        <p class="text-sm text-blue-700 dark:text-blue-300">
            Variables in the template are marked with double curly braces like <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">&#123;&#123;client_name&#125;&#125;</code>. 
            These will be replaced with actual values when generating letters. The <strong>Required Fields</strong> above must be provided, 
            while <strong>Optional Fields</strong> can be left empty if not needed.
        </p>
    </div>

    {{-- Usage Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                {{ $record->letterRequests()->count() }}
            </div>
            <div class="text-sm text-green-700 dark:text-green-300">Total Requests</div>
        </div>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                {{ $record->letterRequests()->where('status', 'completed')->count() }}
            </div>
            <div class="text-sm text-blue-700 dark:text-blue-300">Completed</div>
        </div>
        
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                {{ $record->letterRequests()->where('status', 'failed')->count() }}
            </div>
            <div class="text-sm text-red-700 dark:text-red-300">Failed</div>
        </div>
    </div>
</div> 