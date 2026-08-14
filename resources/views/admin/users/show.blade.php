<x-admin-layout>
    @section('page_title', 'Hunter Profile detail')
    @slot('slot')
        <livewire:admin.user-detail :userId="$userId" />
    @endslot
</x-admin-layout>
