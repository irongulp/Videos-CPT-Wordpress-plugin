(function() {
    tinymce.PluginManager.add( 'custom_link_class', function( editor ) {
        // Add Button to Visual Editor Toolbar
        editor.addButton('custom_link_class', {
            title: 'Insert Video',
            cmd: 'video_shortcode_class',
            image: '/wp-content/plugins/videos-cpt/svg/movie-camera-svgrepo-com.svg',
        });

        // Button clicked
        editor.addCommand('video_shortcode_class', function() {
            // Get the modal
            const modal = document.getElementById("video-shortcode-modal");

            // Get the buttons
            const cancel = document.getElementById("video-cpt-close");
            const insert = document.getElementById("video-cpt-insert");

            // Open the modal
            modal.style.display = "block";

            // When the user clicks on Cancel, close the modal
            cancel.onclick = function() {
                modal.style.display = "none";
            }

            // When the user clicks anywhere outside the modal, close it
            window.onclick = function(event) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            }

            // When the user clicks on Insert, insert shortcode
            insert.onclick = function() {
                const postId = document.getElementById("video-cpt-post-id").value;
                let borderWidth = document.getElementById("video-cpt-border-width").value;
                // Add units if only a number is entered
                if (!isNaN(borderWidth)) {
                    borderWidth = borderWidth + 'px';
                }
                const borderColor = document.getElementById("video-cpt-border-color").value;
                const shortCode =
                    '[prefix_video ' +
                    'id="' + postId + '" ' +
                    'border_width="' + borderWidth + '" ' +
                    'border_color="' + borderColor + '" ' +
                    ']';
                modal.style.display = "none";
                editor.execCommand('mceInsertContent', false, shortCode);
                editor.execCommand('InsertLineBreak', false, null);
            }
        });
    });
})();