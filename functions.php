<?php
/*
Plugin Name: Replace Broken Images
Description: Replace broken images with a placeholder image.
Version: 1.0
Author: Your Name
*/
// Add a custom Admin Page
function add_replace_broken_images_menu()
{
    add_menu_page(
        'Replace Broken Images', // Page title
        'Broken Images', // Menu title
        'manage_options', // Capability
        'replace-broken-images', // Menu slug
        'replace_broken_images_page', // Callback function
        'dashicons-images-alt2', // Icon
        20 // Position in admin menu
    );
}
add_action('admin_menu', 'add_replace_broken_images_menu');

// Callback function for the custom Admin Page
function replace_broken_images_page()
{
    // Check if the button was clicked
    if (isset($_POST['replace_images_action'])) {
        // Perform the replacement process
        replace_broken_images_bulk();
        echo '<div class="notice notice-success"><p>Broken images have been replaced!</p></div>';
    }

    // Display the button
?>
    <div class="wrap">
        <h1>Replace Broken Images</h1>
        <form method="post">
            <?php wp_nonce_field('replace_images_action_nonce', 'replace_images_nonce'); ?>
            <p>
                <input type="submit" name="replace_images_action" class="button-primary" value="Replace Broken Images">
            </p>
        </form>
    </div>
<?php
}


//replace broken images
function replace_broken_images_bulk()
{
    // Default image URL
    $default_image_url = '/wp-content/uploads/default-image.jpg';

    // Get all posts
    $args = [
        'post_type'      => 'any',   // Include all post types
        'posts_per_page' => -1       // Get all posts
    ];
    $posts = get_posts($args);

    foreach ($posts as $post) {
        // Get the post content
        $content = $post->post_content;

        // Find all image tags in the content
        preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $image_url) {
                // Check if the image is broken
                if (!is_image_working($image_url)) {
                    // Replace the broken image with the default image
                    $content = str_replace($image_url, $default_image_url, $content);
                }
            }
        }

        // Update the post content
        wp_update_post([
            'ID'           => $post->ID,
            'post_content' => $content
        ]);
    }
}

// Helper function to check if an image URL is valid
function is_image_working($url)
{
    $headers = @get_headers($url);
    if ($headers && strpos($headers[0], '200 OK') !== false) {
        return true; // Image is valid
    }
    return false; // Image is broken
}
