@php
    $storedImages = $entry ? ($entry->images ?? []) : [];
    $maxImages = 5;
@endphp

@include('crud::fields.inc.wrapper_start')
    <label class="font-weight-bold mb-2">{!! $field['label'] ?? 'Images' !!}</label>

    <div id="listing-existing-images" class="listing-existing-images d-flex flex-wrap mb-3">
        @forelse ($storedImages as $path)
            <div class="listing-image-item position-relative" data-path="{{ $path }}">
                <a href="{{ \App\Models\Listing::resolvePublicUrl($path) }}" target="_blank" rel="noopener noreferrer">
                    <img
                        src="{{ \App\Models\Listing::resolvePublicUrl($path) }}"
                        alt="Listing image"
                        class="listing-image-thumb"
                    >
                </a>
                <button
                    type="button"
                    class="btn btn-sm btn-danger position-absolute remove-listing-image"
                    title="Remove image"
                    data-path="{{ $path }}"
                >
                    <i class="la la-times"></i>
                </button>
            </div>
        @empty
            <p class="text-muted mb-0 no-images-msg">No images yet.</p>
        @endforelse
    </div>

    <div id="listing-clear-images-container"></div>

    <div class="listing-upload-wrapper">
        <label class="listing-upload-zone" for="listing-images-upload">
            <input
                type="file"
                name="images[]"
                accept="image/*"
                multiple
                class="listing-upload-input"
                id="listing-images-upload"
            >
            <div class="listing-upload-zone-inner">
                <span class="listing-upload-icon"><i class="la la-cloud-upload"></i></span>
                <span class="listing-upload-title">Add images</span>
                <span class="listing-upload-hint">Click to browse or drop files here</span>
            </div>
        </label>
        <div id="listing-new-files-preview" class="listing-new-files-preview"></div>
    </div>

    <p class="help-block text-muted mb-0 mt-2">
        Up to {{ $maxImages }} images (JPEG, PNG). Click &times; on a thumbnail to remove an existing image.
    </p>
@include('crud::fields.inc.wrapper_end')

@push('crud_fields_styles')
<style>
    .listing-existing-images {
        gap: 12px;
    }

    .listing-image-item {
        width: 140px;
    }

    .listing-image-thumb {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        display: block;
    }

    .listing-image-item .remove-listing-image {
        top: 6px;
        right: 6px;
        padding: 2px 7px;
        line-height: 1;
        border-radius: 50%;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
    }

    .listing-upload-wrapper {
        max-width: 480px;
    }

    .listing-upload-zone {
        display: block;
        margin: 0;
        cursor: pointer;
        border: 2px dashed #c8d3e0;
        border-radius: 10px;
        background: #f8fafc;
        transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .listing-upload-zone:hover,
    .listing-upload-zone:focus-within {
        border-color: #467fd0;
        background: #f0f5ff;
        box-shadow: 0 0 0 3px rgba(70, 127, 208, 0.12);
    }

    .listing-upload-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .listing-upload-zone-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 28px 20px;
        text-align: center;
        pointer-events: none;
    }

    .listing-upload-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #e8eef8;
        color: #467fd0;
        font-size: 26px;
        line-height: 1;
    }

    .listing-upload-title {
        font-size: 15px;
        font-weight: 600;
        color: #3c4b64;
    }

    .listing-upload-hint {
        font-size: 13px;
        color: #8a9bab;
    }

    .listing-upload-zone.is-dragover {
        border-color: #467fd0;
        background: #e8f0fe;
    }

    .listing-upload-zone.has-files {
        border-style: solid;
        border-color: #9bb8e8;
        background: #f0f5ff;
    }

    .listing-new-files-preview {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 10px;
    }

    .listing-new-file-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        font-size: 13px;
        color: #3c4b64;
        background: #fff;
        border: 1px solid #e4e7ea;
        border-radius: 6px;
    }

    .listing-new-file-item i {
        color: #467fd0;
        font-size: 18px;
    }

    .listing-new-file-item span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endpush

@push('crud_fields_scripts')
<script>
    (function () {
        const container = document.getElementById('listing-existing-images');
        const clearContainer = document.getElementById('listing-clear-images-container');
        const fileInput = document.getElementById('listing-images-upload');
        const uploadZone = document.querySelector('.listing-upload-zone');
        const preview = document.getElementById('listing-new-files-preview');
        const maxImages = {{ $maxImages }};
        const initialCount = {{ count($storedImages) }};

        function currentCount() {
            return container.querySelectorAll('.listing-image-item').length;
        }

        function clearedCount() {
            return clearContainer.querySelectorAll('input').length;
        }

        function updateNoImagesMessage() {
            const msg = container.querySelector('.no-images-msg');
            if (currentCount() === 0 && !msg) {
                container.innerHTML = '<p class="text-muted mb-0 no-images-msg">No images yet.</p>';
            } else if (currentCount() > 0 && msg) {
                msg.remove();
            }
        }

        function renderNewFilesPreview() {
            preview.innerHTML = '';
            const files = fileInput.files;

            if (!files || files.length === 0) {
                uploadZone.classList.remove('has-files');
                return;
            }

            uploadZone.classList.add('has-files');

            Array.from(files).forEach(function (file) {
                const row = document.createElement('div');
                row.className = 'listing-new-file-item';
                row.innerHTML = '<i class="la la-file-image-o"></i><span></span>';
                row.querySelector('span').textContent = file.name;
                preview.appendChild(row);
            });
        }

        container.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-listing-image');
            if (!btn) return;

            e.preventDefault();
            const path = btn.getAttribute('data-path');
            const item = btn.closest('.listing-image-item');

            if (path) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'clear_images[]';
                input.value = path;
                clearContainer.appendChild(input);
            }

            if (item) item.remove();
            updateNoImagesMessage();
        });

        fileInput.addEventListener('change', function () {
            const selected = this.files ? this.files.length : 0;
            const allowed = maxImages - (initialCount - clearedCount());

            if (selected > allowed) {
                alert('You can add at most ' + allowed + ' more image(s) (' + maxImages + ' total per listing).');
                this.value = '';
                renderNewFilesPreview();
                return;
            }

            renderNewFilesPreview();
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            uploadZone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            uploadZone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.classList.remove('is-dragover');
            });
        });

        uploadZone.addEventListener('drop', function (e) {
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    })();
</script>
@endpush
