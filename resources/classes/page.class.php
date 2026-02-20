<?php

class page
{
	public $section;				// page section
	public $title;					// page title
	public $heading;				// page heading
	public $url;					// page URL
    public $path;           		// The actual page path
	public $file;					// page file include
	public $omit_header;			// whether to omit page header
	public $omit_footer;			// whether to omit page footer
	public $omit_content_header;	// Useful for retaining the HTML header, but nothing else
	public $pages;					// pages data
	public $page;					// stores the page data
	public $data_page;				// stores the data-page attribute
    public $gated;		    		// Whether or not the page is gated
    public $gated_page;				// Store the file include for the gate page
	public $sections;				// contains section information
	public $css;					// CSS files included for this page only
	public $js;						// JS files included for this page only
	public $omit_gtm;				// Allows the omission of Google services
	public $meta_description;		// Stores a page's meta description
	public $header_class;			// Capture classes for the header
    public $header_width;			// Set the header width
	public $site_map_include;		// Whether to include this page in the site map

	public $GLOBALS;

	/**
	 *  CONSTRUCTOR
	 *
	 *  This instantiates new instances
	 *  of class Page.
	 *
	 */
	function __construct($url)
	{
		/**
		 *  Include section and page data
		 */
		require_once(CONTENT . '/pagedata.content.php');
		$this->pages = $pages;
		$this->url = $url;

		/**
		 *  Determines the current page.
		 *  Returns the 404 page if no page is identified
		 */

		if (is_null($this->page = $this->getPageKey('url',$this->url)))
		{
			$this->page = $this->getPageKey('url','404');
		}
		/**
		 *  After the page is instantiated, set the data
		 */
		try
		{
			$this->setCurrentPageData();
		}
		catch (Exception $e)
		{
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}

	/**
	 *  Loop through the page router array to
	 *  determine the current page.
	 */
	private function getAllPages()
	{
		foreach ($this->pages as $key => $value)
		{
			foreach ($value as $key2 => $value2)
			{

			}
		}
	}

	/**
	 *  This will retrieve the page key, given another
	 *  key parameter (url, title, etc).
	 */
	public function getPageKey($identifier,$value, $setUrl = FALSE)
	{
		foreach($this->pages as $key => $data)
		{
			foreach ($data as $key2 => $data2)
			{
				if ($key2 == $identifier && in_array($value, $data2))
				{
                    // Set the page path
					$this->path = $value;
                    return $key;
				}
			}
		}
	}

    /**
	 *  Sets the page URL
	 */
	public function set_url($identifier,$value)
	{
		foreach($this->pages as $key => $data)
		{
			foreach ($data as $key2 => $data2)
			{
				if ($key2 == $identifier && in_array($value, $data2))
				{
					$this->url = $value;
				}
			}
		}
	}

	/**
	 *  Retrieves a page's title
	 */
	public function page_get_title($flag = NULL)
	{
		switch ($flag)
		{
			case 'page':
				return $this->title;
			break;
			case 'full':
			default:
				if (empty($this->url))
				{
					return $GLOBALS['env']['sitename'];
				}
				else
				{
					return $this->title. ' | '.$GLOBALS['env']['sitename'];
				}
		}
	}

	/**
	 *  Retrieves the associated content file associated with this page
	 */
	public function page_get_content($gated = false)
	{
        if ($gated == true)
        {
            return "admin_gate.php";
        }
        else
        {
            return $this->file;
        }
	}

	/**
	 *  Retrieves the URL of the current page
	 */
	public function page_get_url($flag = NULL)
	{
        switch ($flag)
        {
            case "full":
                $return = $GLOBALS['env']['protocol'] . '://'.$GLOBALS['env']['host'].'/'.$this->url;
            break;
            default:
                $return = $this->path;
        }
		return $return;
	}

    public function page_is_gated()
    {
        if ($this->gated == TRUE)
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

	/**
	 *  Retrieves the site/short URL of the current page
	 */
	public function page_get_short_url()
	{
		if (isset($this->url))
		{
			return $this->url;
		}
	}

	/**
	 *  Assign each value of the page router
	 *  to a class variable.
	 */
	private function setCurrentPageData()
	{
		foreach ($this->pages[$this->page] as $key => $value)
		{
			$this->$key = $value;
		}
	}

	public function page_set_title($title)
	{
		if (!empty($title))
		{
			$this->title = $title;
		}
	}

	/**
	 *  Returns all data for a specific page, given the page key
	 */
	public function getPageData($pagekey)
	{
		foreach ($this->pages[$pagekey] as $key => $value)
		{
			$newpage[$key] = $value;
		}
		return $newpage;
	}

    /**
	 *  Retrieves the associated page metadata
	 */
	public function page_get_property($field)
	{
        if (isset($this->$field))
        {
            switch ($field)
            {
                case "header_class":
                    $return = " " . $this->$field;
                break;
                default:
                    $return = $this->$field;

            }
            return $return;
        }
        else
        {
            return false;
        }
	}

	/**
	 *  Return all children pages for a given page.
	 */
	public function getChildren()
	{
		$children = array();
		foreach ($this->pages as $key => $value)
		{
			$i = 0;
			foreach ($value as $key2 => $value2)
			{
				if ( $key2 == 'parent' && $value2 == $this->url)
				{
					$children[$i] = $key;
				}
			}
		}
		return $children;
	}
	/**
	 *  Similar to getChildren, this returns all children
	 *  pages for a given section.
	 */
	public function getSubNavItems()
	{
		$subNavItems = array();
		$i = 0;
		foreach ($this->pages as $key => $value)
		{
			if (array_key_exists('parent',$value))
			{
				foreach ($value as $key2 => $value2)
				{
					if ($key2 == 'section' && $value2 == $this->section)
					{
						$subNavItems[$i] = $key;
					}
					$i++;
				}
			}
		}
		return $subNavItems;
	}
	/**
	 *  Prints the sub navigation for a given section
	 *  Accepts one argument: scope
	 *    + all = all sub-menu items
	 *    + children = prints only children pages of the current page
	 */
	public function printSubNav($scope = 'all')
	{
		switch ($scope){
			case 'children':
				$scope = $this->getChildren();
			break;
			case 'all':
			default:
				$scope = $this->getSubNavItems();
		}
		if ($scope != NULL)
		{
			$navList = FALSE;
			$navList .= '<ul>';
			foreach ($scope as $key => $value)
			{
				$navItem = $this->getPageData($value);
				$navList .= '<li><a href="/'.$GLOBALS['env']['docroot'].'/'.$navItem['url'].'">'.$navItem['heading'].'</a></li>';
			}
			$navList .= '</ul>';
			print $navList;
		}
		else{ return NULL; }
	}
	public function set_message($text, $type = 'message')
	{
		$message['type'] = $type;
		$message['text'] = $text;
		return $message;
	}
	public function print_message($message)
	{
		print '<div class="message '.$message['type'].'">'.$message['text'].'</div>';
	}

	/**
	 *  Static function for printing textfield items
	 */
	public static function printItem($item)
	{
		if (isset($item))
		{
			return $item;
		}

	}

	/**
	 *  Redirects the user back home --
	 *  usually called because the user
	 *  doesn't have appropriate access
	 */
	static public function redirect_home()
	{
		ob_end_clean();
		header('Location: '.$GLOBALS['env']['docroot'], true);
	}

	/**
	 *  Allows individual pages to set the base roles permitted
	 *  to view the page
	 */
	 public function restrict_access($roles, $priv)
	 {
		 try
		 {
			 if (!is_array($roles))
			 {
				 throw new Exception( 'Error: function "restrict_access" requires parameter 1 to be an array');
			}
			if ($priv->evaluate($roles) == TRUE)
			{
			}
			else
			{
				$this->redirect_home();
			}
		 }
		 catch (Exception $e)
		{
			throw new Exception( 'Error (restrict_access)', 0, $e);
		}
	}

	/**
	 *  Returns a page width to allow for fluid or centered pages
	 */
	public function page_get_headerwidth()
	{
		if (isset($this->header_width) && $this->header_width == "fluid")
		{
			return "container-fluid";
		}
		else
		{
			return "container-xxl";
		}
	}

	/**
	 *  Assigns the CSS class "empty" if the parameter is empty or NULL
	 */
	public function required_label($value)
	{
		if (isset($value) && empty($value))
		{
			return ' empty';
		}
	}

	/**
	 *  Assigns a CSS class based on a number passed to the function
	 */
	public function print_even_odd($number)
	{
		if ($number % 2 == 0)
		{
			return 'even';
		}
		else
		{
			return 'odd';
		}
	}

	/**
	 *  Prints a "select" box of US states
	 */

	public function print_states($fieldName, $selected)
	{
		if (empty($fieldName))
		{
			exit();
		}
		else
		{
			print '<select class="select" name="'.$fieldName.'">';
			foreach ($GLOBALS['states'] as $key => $value)
			{
				if ($selected == $key)
				{
					$select = ' selected';
				}
				else
				{
					$select = NULL;
				}
				print '<option value="'.$key.'"'.$select.'>'.$value.'</option>';
			}
			print '</select>';
		}
	}


	/**
	 *  Allows CSS files to be included on a per-page basis -- saving bandwidth.
	 */
	public function page_include_css($path)
	{
		if (!empty($this->css))
		{
			$include = "";

			foreach ($this->css as $file)
			{
				$include .= "		<link rel=\"stylesheet\" type=\"text/css\" href=\"".$path."/".$file."\">\n";
				if ($file !== end($this->css))
				{
					$include .= "\t\t";
				}
			}
			return $include;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 *  Allows JS files to be included on a per-page basis -- saving bandwidth.
	 */
	public function page_include_js($path)
	{
		if (!empty($this->js))
		{
			$include = "";

			foreach ($this->js as $file)
			{
				$include .= "<script type=\"text/javascript\" src=\"".$path."/".$file."\"></script>\n";
				if ($file !== end($this->js))
				{
					$include .= "\t\t";
				}
			}
			return $include;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 *  Enables the header to be omitted from the page
	 *  load on a per-page basis.
	 */
	public function page_omit_header()
	{
		if (!empty($this->omit_header) && $this->omit_header != FALSE)
		{
			return TRUE;
		}
	}

	/**
	 *  Enables the content header to be omitted from the page, but still retain
	 *  the HTML header, on a per-page basis.
	 */
	public function page_omit_content_header()
	{
		if (!empty($this->omit_content_header) && $this->omit_content_header == TRUE)
		{
			return TRUE;
		}
	}

	/**
	 *  Enables the footer to be omitted from the page
	 *  load on a per-page basis.
	 */
	public function page_omit_footer()
	{
		if (!empty($this->omit_footer) && $this->omit_footer != FALSE)
		{
			return TRUE;
		}
	}

	/**
	 *  Enables Google Tag Manager to be omitted from the page
	 *  load on a per-page basis.
	 */
	public function omit_gtm()
	{
		if (!empty($this->omit_gtm) && $this->omit_gtm == TRUE)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 *  Helps build the forms more efficiently
	 */
	public function page_formfield_helper($questionID)
	{
		$text = 'name="'.$questionID.'" id="'.$questionID.'"';
		return $text;
	}

	/**
	 *  Formats a URL
	 */
	public function page_formatURL($uri)
	{
		if (!empty($uri))
		{
			if (substr($uri, 0, 7) !== 'http://' && substr($uri, 0, 8) !== 'https://')
			{
				$prefix = FALSE;
				$uri_test = 'http://'.$uri;
			}
			else
			{
				$prefix = TRUE;
				$uri_test = $uri;
			}
			if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$uri_test))
			{
				$url = '<a href="'.$uri_test.'" target="_blank">'.$uri.'</a>';
				return $url;
			}
			else
			{
				return $uri;
			}
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 *  Return the data-page
	 */
	public function page_get_datapage()
	{
		return (isset($this->data_page)) ? $this->data_page : false;
	}


	/**
	 *  Return a field if the value is not null
	 */
	public function page_valid_field($field)
	{
		if (!empty($field))
		{
			return $field;
		}
		else
		{
			return false;
		}
	}



	/* * * * * * * * * * * * * * *
	 *
	 *  Class/static methods
	 *
	 * * * * * * * * * * * * * * */

	/**
	 * GET CURRENT PAGE URL
	 */
	public static function getURL()
	{
		/**
		 *  Get current URL from browser
		 */
		$currentURL = htmlspecialchars($_SERVER["REQUEST_URI"]);

		/**
		 *  Remove the preceding slash, if there is one
		 */
		if (substr($currentURL, 0, 1) == "/")
		{
			$currentURL = substr($currentURL, 1);
		}

		/**
		 *  We'll need to filter out the docroot in order
		 *  to determine the page-specific URL
		 *  Weed out the preceding forward slash.
		 */
		if (substr($GLOBALS['env']['docroot'], 0, 1) == "/")
		{
			$newDocRoot = substr($GLOBALS['env']['docroot'], 1);
		}

		/**
		 *  Next, take our new docroot and use it to
		 *  filter the current URL. The result should
		 *  be the page-specific URL
		 */
		$currentURL = str_replace($newDocRoot,"", $currentURL);

		/**
		 *  Removes the '?' and everything behind it
		 */
		if (strstr($currentURL, '?', true) != FALSE)
		{
			$currentURL = strstr($currentURL, '?', true);
		}

		/**
		 *  Checks for and adds trailing slash
		 */
		if ($currentURL != "" && substr($currentURL, -1) == "/")
		{
			$currentURL = substr($currentURL, 0, -1);
		}
		return $currentURL;
	}


} // end class
