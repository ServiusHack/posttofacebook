<?php

return [
  'plugin' => [
    'name' => 'Post To Facebook',
    'description' => 'Automatically post blog posts on Facebook'
  ],
  'permissions' => [
    'configure' => 'Configure'
  ],
  'flash' => [
    'published' => 'Published post at Facebook',
    'scheduled' => 'Scheduled post at Facebook',
    'backdated' => 'Published backdated post at Facebook',
    'failed_posting' => 'Failed posting to Facebook',
    'updated' => 'Updated post at Facebook',
    'scheduled_existing' => 'Scheduled existing post at Facebook',
    'backdated_existing' => 'Backdated existing post at Facebook',
    'failed_updating' => 'Failed updating post at Facebook',
    'deleted' => 'Deleted post at Facebook',
    'failed_deleting' => 'Failed deleting post at Facebook'
  ],
  'settings' => [
    'page_id' => 'Facebook Page ID',
    'page_id_comment' => "Obtain this from Facebook's page info section",
    'page_access_token' => 'Page Access Token',
    'page_access_token_comment' => 'See documentation on how to obtain this',
    'post_page' => 'Blog Post Page',
    'post_page_comment' => 'The page to point to in the facebook post',
  ]
];
