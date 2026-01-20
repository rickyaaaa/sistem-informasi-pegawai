<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Satker') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('satker.update', $satker) }}" method="POST">
                        @method('PUT')
                        @include('satker._form', ['satker' => $satker])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

