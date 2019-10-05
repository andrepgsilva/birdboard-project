<div class="card flex flex-col">
    <h3 
        class="font-normal text-xl mb-3 py-4 -ml-5 border-l-4 border-blue-light pl-4"
    > 
        <a 
            href="{{ $project->path() }}" 
            class="text-black no-underline"
        >
            {{ $project->title }}
        </a>
    </h3>

    <div class="text-grey mb-4 flex-1">
        {{ str_limit($project->description, 100) }}
    </div>

    @can('manage', $project)
        <footer>
            <form method="POST" action="{{ $project->path() }}" class="text-right">
                @csrf
                @method('DELETE')

                <button class="text-xs" type="submit">Delete</button>
            </form>
        </footer>
    @endcan
</div>    