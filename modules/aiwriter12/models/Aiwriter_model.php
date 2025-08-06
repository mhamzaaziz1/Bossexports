<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Aiwriter_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_usage_case(): array
    {
        return [
            'product_description'   => 'Product Description',
            'email_reply'           => 'Email Reply',
            'review_feedback'       => 'Review Feedback',
            'blog_idea'             => 'Blog Idea &amp; Outline',
            'blog_writing'          => 'Blog Section Writing',
            'business_idea'         => 'Business Ideas',
            'proposal_later'        => 'Proposal Later',
            'cover_letter'          => 'Cover Letter',
            'social_ads'            => 'Facebook, Twitter, Linkedin Ads',
            'google_ads'            => 'Google Search Ads',
            'post_idea'             => 'Post &amp; Caption Ideas',
            'comment_reply'         => 'Comment Reply',
            'birthday_wish'         => 'Birthday Wish',
            'seo_meta'              => 'SEO Meta Description',
            'seo_title'             => 'SEO Meta Title',
            'video_des'             => 'Video Description',
            'video_idea'            => 'Video Idea',
        ];
    }


}
