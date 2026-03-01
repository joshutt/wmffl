// tinyMCE.init({
//     // mode: "textareas",
//     // theme: "advanced",
//     selector: 'textarea#body',
//     // remove_linebreaks: true
// });

tinymce.init({
    selector: '#body',
    statusbar: false,
    menubar: true,
    toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist | outdent indent | link image emoticons',
    plugins: 'lists link image emoticons'
})