<?php
/**
 * Clase para generar breadcrumbs (migas de pan) mediante shortcode.
 * Soporta posts, páginas, categorías, etiquetas, página de blog y páginas estáticas.
 *
 * @package Breadcrumb_Shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Breadcrumb_Shortcode {

    public function __construct() {
        add_shortcode( 'breadcrumb', array( $this, 'render_breadcrumb' ) );
    }

    public function render_breadcrumb( $atts ) {
        $atts = shortcode_atts(
            array(
                'separator'   => '»',
                'home_text'   => __( 'Inicio', 'tiempo21-radiovictoria' ),
                'blog_text'   => __( 'Blog', 'tiempo21-radiovictoria' ),
                'show_current' => 'yes',
                'schema'      => 'yes',
            ),
            $atts,
            'breadcrumb'
        );

        $items = array();
        $items[] = $this->get_home_item( $atts['home_text'] );

        if ( is_front_page() ) {
            return ''; // No mostrar breadcrumb en la portada
        } elseif ( is_home() ) {
            $blog_page_id = get_option( 'page_for_posts' );
            if ( $blog_page_id ) {
                $items[] = $this->get_page_item( $blog_page_id );
            } else {
                $items[] = $this->get_simple_item( $atts['blog_text'] );
            }
        } elseif ( is_singular( 'post' ) ) {
            global $post;
            $blog_page_id = get_option( 'page_for_posts' );
            if ( $blog_page_id ) {
                $items[] = $this->get_page_item( $blog_page_id );
            }
            $categories = get_the_category( $post->ID );
            if ( ! empty( $categories ) ) {
                $items[] = $this->get_category_item( $categories[0] );
            }
            if ( 'yes' === $atts['show_current'] ) {
                $items[] = $this->get_simple_item( get_the_title() );
            }
        } elseif ( is_page() ) {
            global $post;
            $ancestors = array_reverse( get_post_ancestors( $post->ID ) );
            foreach ( $ancestors as $ancestor ) {
                $items[] = $this->get_page_item( $ancestor );
            }
            if ( 'yes' === $atts['show_current'] ) {
                $items[] = $this->get_simple_item( get_the_title() );
            }
        } elseif ( is_category() ) {
            $category = get_queried_object();
            $ancestors = array_reverse( get_ancestors( $category->term_id, 'category' ) );
            foreach ( $ancestors as $ancestor_id ) {
                $ancestor = get_category( $ancestor_id );
                $items[] = $this->get_category_item( $ancestor );
            }
            if ( 'yes' === $atts['show_current'] ) {
                $items[] = $this->get_simple_item( single_cat_title( '', false ) );
            }
        } elseif ( is_tag() ) {
            $items[] = $this->get_simple_item( single_tag_title( '', false ) );
        } elseif ( is_tax() ) {
            $term = get_queried_object();
            $items[] = $this->get_simple_item( $term->name );
        } elseif ( is_archive() ) {
            if ( is_day() ) {
                $items[] = $this->get_simple_item( get_the_date() );
            } elseif ( is_month() ) {
                $items[] = $this->get_simple_item( get_the_date( 'F Y' ) );
            } elseif ( is_year() ) {
                $items[] = $this->get_simple_item( get_the_date( 'Y' ) );
            } elseif ( is_author() ) {
                $author = get_queried_object();
                $items[] = $this->get_simple_item( $author->display_name );
            } else {
                $items[] = $this->get_simple_item( __( 'Archivos', 'tiempo21-radiovictoria' ) );
            }
        } elseif ( is_search() ) {
            $items[] = $this->get_simple_item( __( 'Resultados de búsqueda', 'tiempo21-radiovictoria' ) );
        } elseif ( is_404() ) {
            $items[] = $this->get_simple_item( __( 'Error 404', 'tiempo21-radiovictoria' ) );
        }

        $output = '<nav class="breadcrumb" aria-label="Breadcrumb">';
        $output .= ( 'yes' === $atts['schema'] ) ? '<ol itemscope itemtype="https://schema.org/BreadcrumbList">' : '<ol>';

        $position = 1;
        $total_items = count( $items );
        foreach ( $items as $index => $item ) {
            $is_last = ( $index === $total_items - 1 );
            $output .= '<li' . ( 'yes' === $atts['schema'] ? ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"' : '' ) . '>';

            if ( isset( $item['url'] ) && ! $is_last ) {
                $output .= '<a href="' . esc_url( $item['url'] ) . '"' . ( 'yes' === $atts['schema'] ? ' itemprop="item"' : '' ) . '>';
                $output .= '<span' . ( 'yes' === $atts['schema'] ? ' itemprop="name"' : '' ) . '>' . esc_html( $item['text'] ) . '</span>';
                $output .= '</a>';
            } else {
                $output .= '<span' . ( 'yes' === $atts['schema'] ? ' itemprop="item"' : '' ) . '>';
                $output .= '<span' . ( 'yes' === $atts['schema'] ? ' itemprop="name"' : '' ) . '>' . esc_html( $item['text'] ) . '</span>';
                $output .= '</span>';
            }

            if ( 'yes' === $atts['schema'] ) {
                $output .= '<meta itemprop="position" content="' . $position . '" />';
            }

            $output .= '</li>';

            if ( ! $is_last ) {
                $output .= '<li class="breadcrumb-separator" aria-hidden="true">' . esc_html( $atts['separator'] ) . '</li>';
            }

            $position++;
        }

        $output .= '</ol></nav>';
        return $output;
    }

    private function get_home_item( $home_text ) {
        return array( 'url' => home_url( '/' ), 'text' => $home_text );
    }

    private function get_page_item( $page_id, $custom_text = '' ) {
        return array(
            'url'  => get_permalink( $page_id ),
            'text' => $custom_text ?: get_the_title( $page_id ),
        );
    }

    private function get_category_item( $category ) {
        return array(
            'url'  => get_category_link( $category->term_id ),
            'text' => $category->name,
        );
    }

    private function get_simple_item( $text ) {
        return array( 'text' => $text );
    }
}