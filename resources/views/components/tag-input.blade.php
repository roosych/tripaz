{{--
    Tag Input Component
    Props:
      $name        — input name attribute (e.g. "detail[includes][]")
      $placeholder — placeholder text
      $required    — boolean
--}}
@props([
    'name'        => '',
    'placeholder' => 'Add tag (press Enter)',
    'required'    => false,
])

@php
    $componentId = 'tag-' . uniqid();
@endphp

<div class="tag-input-container" id="{{ $componentId }}">
    <span class="tag-real-input" role="textbox" contenteditable="true"
          data-placeholder="{{ $placeholder }}"
          data-name="{{ $name }}"
          style="outline: none; min-height: 24px; padding: 2px 4px;"
          aria-label="{{ $placeholder }}"></span>
</div>

<script>
(function () {
    var container = document.getElementById('{{ $componentId }}');
    var input     = container.querySelector('.tag-real-input');

    // Placeholder behaviour
    input.addEventListener('focus', function () {
        if (!input.textContent) input.dataset.showPlaceholder = 'false';
    });
    input.addEventListener('blur', function () {
        if (!input.textContent) input.dataset.showPlaceholder = 'true';
    });
    input.style.setProperty('--placeholder', '"' + input.dataset.placeholder + '"');

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            var value = input.textContent.trim().replace(/,$/, '');
            if (!value) return;
            addTag(value);
            input.textContent = '';
        } else if (e.key === 'Backspace' && !input.textContent) {
            var tags = container.querySelectorAll('.tag-item');
            if (tags.length) {
                tags[tags.length - 1].remove();
            }
        }
    });

    container.addEventListener('click', function () {
        input.focus();
    });

    function addTag(value) {
        var name = input.dataset.name;
        var tag  = document.createElement('span');
        tag.className = 'tag-item';
        tag.innerHTML = '<span>' + escapeHtml(value) + '</span>' +
                        '<span class="remove-tag" title="Remove">&times;</span>' +
                        '<input type="hidden" name="' + escapeHtml(name) + '" value="' + escapeHtml(value) + '">';
        container.insertBefore(tag, input);

        tag.querySelector('.remove-tag').addEventListener('click', function () {
            tag.remove();
        });
    }

    function escapeHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>

<style>
#{{ $componentId }} .tag-real-input:empty::before {
    content: attr(data-placeholder);
    color: #adb5bd;
    pointer-events: none;
}
</style>
