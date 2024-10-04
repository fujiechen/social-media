<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js?{{time()}}"></script>
<script src="https://formbuilder.online/assets/js/form-builder.min.js?{{time()}}"></script>
<script src="https://formbuilder.online/assets/js/form-render.min.js?{{time()}}"></script>

<script>
    Dcat.ready(function () {
        const fbEditor = document.getElementById("fb-editor");

        let options = {
            controlPosition: 'left',
            disabledActionButtons: ['data'],
            disabledAttrs: [
                'access'
            ],
            disableFields: ['file'],
            dataType: 'xml',
            onSave: function (event, formData) {
                $('#formBody').val(formData)
                $('#inputForm').submit();
            },
            @if (isset($form))
            formData:  '{!! $form->body !!}',
            @endif
            actionButtons: [{
                id: 'preview',
                className: 'btn btn-success',
                label: 'Preview',
                type: 'button',
                events: {
                    click: function () {
                        showPreview(formBuilder.actions.getData('xml'));
                    }
                }
            }]
        };

        let formBuilder = $(fbEditor).formBuilder(options);

        function showPreview(formData) {
            let formRenderOpts = {
                dataType: 'xml',
                formData
            };
            let $renderContainer = $('<form/>');
            $renderContainer.formRender(formRenderOpts);
            let formName = $('#inputForm').find('input[name=name]').val();

            let html = `<!doctype html><title>Form Preview - ` + formName + `</title><body class="container">${$renderContainer.html()}</body></html>`;
            var formPreviewWindow = window.open('', 'formPreview', 'height=768,width=1024,toolbar=no,scrollbars=yes');

            formPreviewWindow.document.write(html);
            var style = document.createElement('link');
            style.setAttribute('href', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
            style.setAttribute('rel', 'stylesheet');
            style.setAttribute('type', 'text/css');
            formPreviewWindow.document.head.appendChild(style);
        }
    });

</script>


<form id="inputForm" class="form-horizontal" action="/admin/account/forms/{{$formId}}" method="post">
    @csrf
    @if(isset($form))
    @method('put')
    @endif
    <div class="form-group row form-field">
        <span>Name</span>
        <input required="1" type="text" name="name" value="{{isset($form) ? $form->name : null}}" class="form-control" placeholder="Input Name">
        <input id="formBody" name="body" hidden="hidden">
    </div>
</form>

<div id="fb-editor"></div>
