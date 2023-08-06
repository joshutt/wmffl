tinyMCE.init({
    selector: '.editableArticle',
    // selector: 'textarea#article',
    // theme : "advanced",
    // plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",
    plugins : "autolink,lists,spellchecker,pagebreak,table,save,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,template,lists,charmap,emoticons,hr,image,link,wordcount",

    // Theme options
    // theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
    // theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
    // theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
    // theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
    // theme_advanced_toolbar_location : "top",
    // theme_advanced_toolbar_align : "left",
    // theme_advanced_statusbar_location : "bottom",

    toolbar: [
        'newdocument | formatselect fontsizeselect | forecolor backcolor | undo redo',
        'bold italic underline strikethrough blockquote removeformat | alignleft aligncenter alignright alignjustify | outdent indent |numlist bullist checklist |',
        'fullscreen preview wordcount | charmap emoticons hr image link media',
        'table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol',
    ],
    theme_advanced_resizing : true,
    remove_linebreaks: true,
    link_title: false,
    link_quicklink: true,
    target_list: false,
    menubar: false,
    statusbar: false,
    inline: true,
});
