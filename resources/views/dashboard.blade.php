<x-admin>
    @section('title','Dashboard')
     <x-dashboard :days="request('days', 7)" />
</x-admin>
