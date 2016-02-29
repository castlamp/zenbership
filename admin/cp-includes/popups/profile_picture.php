<script type="text/javascript" src="js/jquery.fileuploader.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var uploader = new qq.FileUploader({
            element: document.getElementById('fileuploader'),
            action: 'cp-functions/upload.php',
            debug: true,
            allowedExtensions: ['jpg', 'jpeg', 'png'],
            onComplete: function (id, fileName, responseJSON) {
                string = 'type=profile-picture&id=' + responseJSON.id + '&filename=' + responseJSON.filename;
                popup('crop_image', string, '1');
            },
            params: {
                type: '<?php echo $_POST['type']; ?>',
                id: '<?php echo $_POST['id']; ?>',
                permission: 'upload-profile',
                label: 'profile-picture',
                scope: '1' // 1 = admin cp only, 0 = user page as well
            }
        });
    });
</script>

<h1>Upload a Profile Picture</h1>

<div class="pad24 popupbody">
    <p>Drag and drop an image to upload a profile picture.</p>

    <div id="fileuploader">
        <noscript><p>Please enable JavaScript to use file uploader.</p></noscript>
    </div>
</div>