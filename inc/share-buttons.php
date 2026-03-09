<?php
/**
 * Plugin Name: Custom Share Buttons
 * Description: Social media share buttons system with shortcodes
 * Version: 1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ========================
// SHARE BUTTONS SYSTEM
// ========================

// Main shortcode for share buttons
function t21_share_buttons_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => 'Share this content',
        'alignment' => 'center',
        'size' => '40',
        'networks' => 'facebook,x,whatsapp,telegram,email,print',
        'style' => 'simple'
    ), $atts);
    
    // Support both Spanish and English values for backward compatibility
    $style_mapping = array(
        'circulo' => 'circle',
        'cuadrado' => 'square',
        'simple' => 'simple'
    );
    if (isset($style_mapping[$atts['style']])) {
        $atts['style'] = $style_mapping[$atts['style']];
    }
    
    // Support 'align' for backward compatibility
    if (empty($atts['alignment']) && !empty($atts['align'])) {
        $atts['alignment'] = $atts['align'];
    }
    
    $url = urlencode(get_permalink());
    $post_title = urlencode(get_the_title());
    
    // Social networks configuration with SVG
    $available_networks = array(
        'facebook' => array(
            'url' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
            'color' => '#1877f2',
            'svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
            'name' => 'Facebook',
            'label' => 'Share on Facebook'
        ),
        'x' => array(
            'url' => "https://twitter.com/intent/tweet?url={$url}&text={$post_title}",
            'color' => '#000000',
            'svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            'name' => 'X',
            'label' => 'Share on X'
        ),
        'whatsapp' => array(
            'url' => "https://api.whatsapp.com/send?text={$post_title}%20{$url}",
            'color' => '#25d366',
            'svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893-.001-3.189-1.262-6.189-3.553-8.449"/></svg>',
            'name' => 'WhatsApp',
            'label' => 'Share on WhatsApp'
        ),
        'telegram' => array(
            'url' => "https://t.me/share/url?url={$url}&text={$post_title}",
            'color' => '#0088cc',
            'svg' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.374 0 0 5.373 0 12s5.374 12 12 12 12-5.373 12-12S18.626 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.14.14-.26.26-.534.26l.213-3.053 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.136-.954l11.566-4.458c.538-.196 1.006.128.832.941z"/></svg>',
            'name' => 'Telegram',
            'label' => 'Share on Telegram'
        )
    );
    
    // Process selected networks
    $selected_networks = explode(',', $atts['networks']);
    $icon_size = intval($atts['size']);
    
    // Generate HTML
    $output = '<div class="t21-share-buttons">';
    
    // Title
    if (!empty($atts['title'])) {
        $output .= '<div class="t21-share-buttons-title">' . esc_html($atts['title']) . '</div>';
    }
    
    $output .= '<div class="t21-share-buttons-container">';
    
    foreach ($selected_networks as $network) {
        $network = trim($network);
        if (isset($available_networks[$network])) {
            $network_data = $available_networks[$network];
            
            // On mobile, replace "print" with "share"
            if ($network === 'print' && wp_is_mobile()) {
                $network_data = $available_networks['share'];
            }
            
            // Generate each button based on style
            $button_style = '';
            $button_classes = '';
            
            switch ($atts['style']) {
                case 'circle':
                    $button_style = 'display: inline-flex; align-items: center; justify-content: center; text-decoration: none; width: ' . ($icon_size + 16) . 'px; height: ' . ($icon_size + 16) . 'px; border-radius: 50%; background-color: ' . $network_data['color'] . '; color: white;';
                    $svg_color = 'white';
                    break;
                case 'square':
                    $button_style = 'display: inline-flex; align-items: center; justify-content: center; text-decoration: none; width: ' . ($icon_size + 16) . 'px; height: ' . ($icon_size + 16) . 'px; border-radius: 4px; background-color: ' . $network_data['color'] . '; color: white;';
                    $svg_color = 'white';
                    break;
                default: // simple
                    $button_style = 'display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: ' . $network_data['color'] . ';';
                    $svg_color = $network_data['color'];
                    $button_classes = 't21-share-btn-simple';
            }
            
            // Replace color in SVG
            $svg_icon = str_replace('currentColor', $svg_color, $network_data['svg']);
            // Adjust SVG size
            $svg_icon = str_replace('width="24"', 'width="' . $icon_size . '"', $svg_icon);
            $svg_icon = str_replace('height="24"', 'height="' . $icon_size . '"', $svg_icon);
            
            // Extra attributes for native share button
            $extra_attributes = '';
            if ($network === 'share' || ($network === 'print' && wp_is_mobile())) {
                $button_classes .= ' t21-share-native';
                $extra_attributes = ' onclick="t21ShareNative()"';
            }
            
            $button = '<a href="' . esc_url($network_data['url']) . '" 
                        class="' . $button_classes . '"
                        target="_blank" 
                        rel="noopener noreferrer nofollow"
                        aria-label="' . esc_attr($network_data['label']) . '"
                        title="' . esc_attr($network_data['label']) . '"
                        style="' . $button_style . '"' . $extra_attributes . '>';
            
            $button .= $svg_icon;
            $button .= '</a>';
            
            $output .= $button;
        }
    }
    
    $output .= '</div></div>';
    
    // Add JavaScript for native sharing
    $output .= '
    <script>
    function t21ShareNative() {
        if (navigator.share) {
            navigator.share({
                title: "' . esc_js(get_the_title()) . '",
                text: "' . esc_js(wp_strip_all_tags(get_the_excerpt())) . '",
                url: "' . esc_js(get_permalink()) . '"
            })
            .then(() => console.log("Content shared successfully"))
            .catch((error) => console.log("Error sharing:", error));
        } else {
            alert("Your browser does not support the native share function.");
        }
    }
    
    function t21IsMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    
    document.addEventListener("DOMContentLoaded", function() {
        if (t21IsMobile()) {
            console.log("Mobile mode activated - Native share available");
        }
    });
    </script>
    
    <style>
    .t21-share-native {
        cursor: pointer;
    }
    
    .t21-share-native:hover {
        opacity: 0.8;
    }
    
    @media (max-width: 768px) {
        .t21-share-buttons-container {
            gap: 10px !important;
        }
        
        .t21-share-btn-simple[href*="javascript:window.print"] {
            display: none !important;
        }
    }
    
    @media (max-width: 480px) {
        .t21-share-buttons-container {
            gap: 8px !important;
        }
        
        .t21-share-buttons {
            margin: 15px 0 !important;
        }
    }
    </style>
    ';
    
    return $output;
}
add_shortcode('t21_share_buttons', 't21_share_buttons_shortcode');

// Simplified shortcode
function t21_share_simple() {
    return do_shortcode('[t21_share_buttons]');
}
add_shortcode('share', 't21_share_simple');
