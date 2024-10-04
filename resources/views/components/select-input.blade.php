
    @props(['disabled' => false, 'options' => [], 'selected' => null, 'default' => null])

    <select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) !!}>
        <option value="">Select</option>
        @foreach($options as $key => $value)
            <option value="{{ $key }}" @selected($selected === $key || $default === $value)>{{ $value }}</option>
        @endforeach
    </select>

