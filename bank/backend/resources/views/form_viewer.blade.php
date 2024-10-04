<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<script src="https://formbuilder.online/assets/js/form-builder.min.js"></script>
<script src="https://formbuilder.online/assets/js/form-render.min.js"></script>

<script>
    Dcat.ready(function () {
        var fbTemplate = document.getElementById('fb-template');
        $('.fb-render').formRender({
            dataType: 'xml',
            formData: '{!! $form->body !!}'
        });
    });
</script>

<h1>{{ $form->name }}</h1>
<div class="fb-render"></div>
