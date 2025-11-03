@props(['fieldName'])

@error($fieldName)
    <small class="text-danger">{{ $message }}</small>
@enderror