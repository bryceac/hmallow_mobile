<?php

/**
 * MyTheme is a custom Theme class for the K2 theme.
 *
 * @package Habari
 */

// We must tell Habari to use MyTheme as the custom theme class:
define( 'THEME_CLASS', 'MyTheme' );

/**
 * A custom theme for K2 output
 */
class MyTheme extends Theme
{
	/**
	 * Execute on theme init to apply these filters to output
	 */
	public function action_init_theme()
	{
		// Apply Format::autop() to post content...
		Format::apply( 'autop', 'post_content_out' );
		// Apply Format::autop() to comment content...
		Format::apply( 'autop', 'comment_content_out' );
		// Apply Format::tag_and_list() to post tags...
		Format::apply( 'tag_and_list', 'post_tags_out' );
		// Apply Format::nice_date() to post date...
		Format::apply( 'nice_date', 'post_pubdate_out', 'F j, Y g:ia' );
		
		// Remove the comment on the following line to limit post length on the home page to 1 paragraph or 100 characters
		//Format::apply_with_hook_params( 'more', 'post_content_out', _t('more'), 100, 1 );
	}

	/**
	 * Add additional template variables to the template output.
	 *
	 *  You can assign additional output values in the template here, instead of
	 *  having the PHP execute directly in the template.  The advantage is that
	 *  you would easily be able to switch between template types (RawPHP/Smarty)
	 *  without having to port code from one to the other.
	 *
	 *  You could use this area to provide "recent comments" data to the template,
	 *  for instance.
	 *
	 *  Note that the variables added here should possibly *always* be added,
	 *  especially 'user'.
	 *
	 *  Also, this function gets executed *after* regular data is assigned to the
	 *  template.  So the values here, unless checked, will overwrite any existing
	 *  values.
	 */
	public function add_template_vars()
	{
		//Theme Options
		
		$this->assign('home_tab','Home'); //Set to whatever you want your first tab text to be.
		$this->assign( 'show_author' , false ); //Display author in posts 
		
		
		if( !$this->template_engine->assigned( 'pages' ) ) {
			$this->assign('pages', Posts::get( array( 'content_type' => 'page', 'status' => Post::status('published'), 'nolimit' => 1 ) ) );
		}
		if( !$this->template_engine->assigned( 'page' ) ) {
			$page = Controller::get_var( 'page' );
			$this->assign('page', isset( $page ) ? $page : 1 );
		}
		
		parent::add_template_vars();
		
		//from mzingi
			//visiting page/2, /3 will offset to the next page of posts in the sidebar
			$page =Controller::get_var( 'page' );
			$pagination =Options::get('pagination');
			if ( $page == '' ) { $page = 1; }
			$this->assign( 'more_posts', Posts::get(array ( 'status' => 'published','content_type' => 'entry', 'offset' => ($pagination)*($page), 'limit' => 5,  ) ) );

			//from mzingi			
			//for recent comments loop in sidebar.php
			$this->assign('recent_comments', Comments::get( array('limit'=>5, 'status'=>Comment::STATUS_APPROVED, 'orderby'=>'date DESC' ) ) );
		
	}

	// place to holder header content
	public function action_template_header($theme)
	{
		Stack::add('template_stylesheet', $theme->get_url('/style.css'));
		Stack::add( 'template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery' );
		Stack::add( 'template_header_javascript', array($this->hmallow_script(), 'type="text/javascript"'));
	}
	
	// move Javascript from header file to theme function
	private function hmallow_script()
	{
		$mscript = "$(document).ready(function() {
$('#header a#menu').click( function() {
    $('#supernavcontainer').toggle();
   });
});

$(document).ready(function() {
$('#commentb').click( function() {
    $('#commentarea').toggle();
   });
});";
		return $mscript;
	}
	
	public function k2_comment_class( $comment, $post )
	{
		$class= 'class="comment';
		if ( $comment->status == Comment::STATUS_UNAPPROVED ) {
			$class.= '-unapproved';
		}
		// check to see if the comment is by a registered user
		if ( $u= User::get( $comment->email ) ) {
			$class.= ' byuser comment-author-' . Utils::slugify( $u->displayname );
		}
		if( $comment->email == $post->author->email ) {
			$class.= ' bypostauthor';
		}

		$class.= '"';
		return $class;
	}

}

?>
