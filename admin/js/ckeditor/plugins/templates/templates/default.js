/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

// Register a templates definition set named "default".
CKEDITOR.addTemplates( 'default', {
    // The name of sub folder which hold the shortcut preview images of the
    // templates.
    imagesPath: CKEDITOR.getUrl( CKEDITOR.plugins.getPath( 'templates' ) + 'templates/images/' ),

    // The templates definitions.
    templates: [
        {
            title: 'Two Columns: 50-50',
            image: 'two_col_5050.png',
            description: 'Two columns: left 50%, right 50%',
            html: '<table cellspacing="0" cellpadding="0" style="width:100%" border="0">' +
                '<tr>' +
                '<td style="width:50%" valign="top">' +
                'Column 1' +
                '</td>' +
                '<td style="width:50%" valign="top">' +
                'Column 2' +
                '</td>' +
                '</tr></table>'
        },
        {
            title: 'Two Columns: 66-34',
            image: 'two_col_6634.png',
            description: 'Two columns: left 66%, right 34%',
            html: '<table cellspacing="0" cellpadding="0" style="width:100%" border="0">' +
                '<tr>' +
                '<td style="width:66%" valign="top">' +
                'Column 1' +
                '</td>' +
                '<td style="width:34%" valign="top">' +
                'Column 2' +
                '</td>' +
                '</tr></table>'
        },
        {
            title: 'Two Columns: 34-66',
            image: 'two_col_3466.png',
            description: 'Two columns: left 34%, right 66%',
            html: '<table cellspacing="0" cellpadding="0" style="width:100%" border="0">' +
                '<tr>' +
                '<td style="width:34%" valign="top">' +
                'Column 1' +
                '</td>' +
                '<td style="width:66%" valign="top">' +
                'Column 2' +
                '</td>' +
                '</tr></table>'
        },
        {
            title: 'Three Columns: 33-34-33',
            image: 'two_col_333333.png',
            description: 'Two columns: left 33%, middle 34%, right 33%',
            html: '<table cellspacing="0" cellpadding="0" style="width:100%" border="0">' +
                '<tr>' +
                '<td style="width:33%" valign="top">' +
                'Column 1' +
                '</td>' +
                '<td style="width:34%" valign="top">' +
                'Column 2' +
                '</td>' +
                '<td style="width:33%" valign="top">' +
                'Column 3' +
                '</td>' +
                '</tr></table>'
        },
        {
            title: 'Image and Title',
            image: 'template1.gif',
            description: 'One main image with a title and text that surround the image.',
            html: '<h3>' +
                '<img style="margin-right: 10px" height="100" width="100" align="left"/>' +
                'Type the title here' +
                '</h3>' +
                '<p>' +
                'Type the text here' +
                '</p>'
        }
    ]
});
