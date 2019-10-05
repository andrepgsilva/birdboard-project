@if ($errors->{ $bag ?? 'default' }->any())
    <ul class="mt-6 list-disc">
        @foreach ($errors->{ $bag ?? 'default' }->all() as $error)
            <li class="text-sm text-redd">{{ $error }}</li>
        @endforeach
    </ul>
@endif