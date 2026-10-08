@php
$map = [
    'Submitted'          => 'badge-submitted',
    'Pending Validation' => 'badge-pending',
    'Approved'           => 'badge-approved',
    'Assigned to Office' => 'badge-assigned',
    'In Progress'        => 'badge-inprogress',
    'Resolved'           => 'badge-resolved',
    'Rejected'           => 'badge-rejected',
];
$cls = $map[$status] ?? 'badge-submitted';
@endphp
<span class="badge {{ $cls }}">{{ $status }}</span>
