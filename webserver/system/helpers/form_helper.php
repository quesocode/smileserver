<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2009, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Form Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/helpers/form_helper.html
 */

// ------------------------------------------------------------------------

/**
 * Form Declaration
 *
 * Creates the opening portion of the form.
 *
 * @access	public
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 * @return	string
 */	
if ( ! function_exists('form_open'))
{
	function form_open($action = '', $attributes = '', $hidden = array())
	{
		$CI =& get_instance();

		if ($attributes == '')
		{
			$attributes = 'method="post"';
		}

		$action = ( strpos($action, '://') === FALSE) ? $CI->config->site_url($action) : $action;

		$form = '<form action="'.$action.'"';
	
		$form .= _attributes_to_string($attributes, TRUE);
	
		$form .= '>';

		if (is_array($hidden) AND count($hidden) > 0)
		{
			$form .= form_hidden($hidden);
		}

		return $form;
	}
}

// ------------------------------------------------------------------------

/**
 * Form Declaration - Multipart type
 *
 * Creates the opening portion of the form, but with "multipart/form-data".
 *
 * @access	public
 * @param	string	the URI segments of the form destination
 * @param	array	a key/value pair of attributes
 * @param	array	a key/value pair hidden data
 * @return	string
 */
if ( ! function_exists('form_open_multipart'))
{
	function form_open_multipart($action, $attributes = array(), $hidden = array())
	{
		$attributes['enctype'] = 'multipart/form-data';
		return form_open($action, $attributes, $hidden);
	}
}

// ------------------------------------------------------------------------

/**
 * Hidden Input Field
 *
 * Generates hidden fields.  You can pass a simple key/value string or an associative
 * array with multiple values.
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_hidden'))
{
	function form_hidden($name, $value = '', $recursing = FALSE)
	{
		static $form;

		if ($recursing === FALSE)
		{
			$form = "\n";
		}

		if (is_array($name))
		{
			foreach ($name as $key => $val)
			{
				form_hidden($key, $val, TRUE);
			}
			return $form;
		}

		if ( ! is_array($value))
		{
			$form .= '<input type="hidden" name="'.$name.'" value="'.form_prep($value, $name).'" />'."\n";
		}
		else
		{
			foreach ($value as $k => $v)
			{
				$k = (is_int($k)) ? '' : $k; 
				form_hidden($name.'['.$k.']', $v, TRUE);
			}
		}

		return $form;
	}
}

// ------------------------------------------------------------------------

/**
 * Text Input Field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_input'))
{
	function form_input($data = '', $value = '', $extra = '')
	{
		$defaults = array('type' => 'text', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

		return "<input "._parse_form_attributes($data, $defaults).$extra." />";
	}
}

// ------------------------------------------------------------------------

/**
 * Password Field
 *
 * Identical to the input function but adds the "password" type
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_password'))
{
	function form_password($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'password';
		return form_input($data, $value, $extra);
	}
}

// ------------------------------------------------------------------------

/**
 * Upload Field
 *
 * Identical to the input function but adds the "file" type
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_upload'))
{
	function form_upload($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'file';
		return form_input($data, $value, $extra);
	}
}

// ------------------------------------------------------------------------

/**
 * Textarea field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_textarea'))
{
	function form_textarea($data = '', $value = '', $extra = '')
	{
		$defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'cols' => '90', 'rows' => '12');

		if ( ! is_array($data) OR ! isset($data['value']))
		{
			$val = $value;
		}
		else
		{
			$val = $data['value']; 
			unset($data['value']); // textareas don't use the value attribute
		}
		
		$name = (is_array($data)) ? $data['name'] : $data;
		return "<textarea "._parse_form_attributes($data, $defaults).$extra.">".form_prep($val, $name)."</textarea>";
	}
}

// ------------------------------------------------------------------------

/**
 * Multi-select menu
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	mixed
 * @param	string
 * @return	type
 */
if (! function_exists('form_multiselect'))
{
	function form_multiselect($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if ( ! strpos($extra, 'multiple'))
		{
			$extra .= ' multiple="multiple"';
		}
		
		return form_dropdown($name, $options, $selected, $extra);
	}
}

// --------------------------------------------------------------------

/**
 * Drop-down Menu
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_dropdown'))
{
	function form_dropdown($name = '', $options = array(), $selected = array(), $extra = '')
	{
		if ( ! is_array($selected))
		{
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (count($selected) === 0)
		{
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name]))
			{
				$selected = array($_POST[$name]);
			}
		}

		if ($extra != '') $extra = ' '.$extra;

		$multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

		$form = '<select name="'.$name.'"'.$extra.$multiple.">\n";

		foreach ($options as $key => $val)
		{
			$key = (string) $key;

			if (is_array($val))
			{
				$form .= '<optgroup label="'.$key.'">'."\n";

				foreach ($val as $optgroup_key => $optgroup_val)
				{
					$sel = (in_array($optgroup_key, $selected)) ? ' selected="selected"' : '';

					$form .= '<option value="'.$optgroup_key.'"'.$sel.'>'.(string) $optgroup_val."</option>\n";
				}

				$form .= '</optgroup>'."\n";
			}
			else
			{
				$sel = (in_array($key, $selected)) ? ' selected="selected"' : '';

				$form .= '<option value="'.$key.'"'.$sel.'>'.(string) $val."</option>\n";
			}
		}

		$form .= '</select>';

		return $form;
	}
}


// ------------------------------------------------------------------------

/**
 * Form State Drop Down Menu
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	string
 * @param	array
 * @return	string
 */
function form_state ($name = '', $selected = array(), $extra = '', $options = array())
{
  $options = !empty($options) ? $options : array(''=>'--', 'AL'=>"Alabama",  
    			'AK'=>"Alaska",  
    			'AZ'=>"Arizona",  
    			'AR'=>"Arkansas",  
    			'CA'=>"California",  
    			'CO'=>"Colorado",  
    			'CT'=>"Connecticut",  
    			'DE'=>"Delaware",  
    			'DC'=>"District Of Columbia",  
    			'FL'=>"Florida",  
    			'GA'=>"Georgia",  
    			'HI'=>"Hawaii",  
    			'ID'=>"Idaho",  
    			'IL'=>"Illinois",  
    			'IN'=>"Indiana",  
    			'IA'=>"Iowa",  
    			'KS'=>"Kansas",  
    			'KY'=>"Kentucky",  
    			'LA'=>"Louisiana",  
    			'ME'=>"Maine",  
    			'MD'=>"Maryland",  
    			'MA'=>"Massachusetts",  
    			'MI'=>"Michigan",  
    			'MN'=>"Minnesota",  
    			'MS'=>"Mississippi",  
    			'MO'=>"Missouri",  
    			'MT'=>"Montana",
    			'NE'=>"Nebraska",
    			'NV'=>"Nevada",
    			'NH'=>"New Hampshire",
    			'NJ'=>"New Jersey",
    			'NM'=>"New Mexico",
    			'NY'=>"New York",
    			'NC'=>"North Carolina",
    			'ND'=>"North Dakota",
    			'OH'=>"Ohio",  
    			'OK'=>"Oklahoma",  
    			'OR'=>"Oregon",  
    			'PA'=>"Pennsylvania",  
    			'RI'=>"Rhode Island",  
    			'SC'=>"South Carolina",  
    			'SD'=>"South Dakota",
    			'TN'=>"Tennessee",  
    			'TX'=>"Texas",  
    			'UT'=>"Utah",  
    			'VT'=>"Vermont",  
    			'VA'=>"Virginia",  
    			'WA'=>"Washington",  
    			'WV'=>"West Virginia",  
    			'WI'=>"Wisconsin",  
    			'WY'=>"Wyoming");

    	  return form_dropdown($name, $options, $selected, $extra);
}



// ------------------------------------------------------------------------

/**
 * Build Form Drop Down Menu for Doctrine Model
 *
 * @access	public
 * @param object      Doctrine Query Object (optional) - defaults to this if both $query and $model are present
 * @param string      Doctrine Model Name (optional)
 * @param string      the value attribute for each select option
 * @param string      the displayed string for each select option
 * @param	string      the name attribue for the select
 * @param	array       the selected entry in the select
 * @param	string      
 * @param	array
 * @return	string
 */
function form_doctrine_drop_down ($query = NULL, $model = NULL, $key = 'id', $value = 'title', $name = '', $selected = array(), $extra = '', $options = array())
{
  if (isset($query) && !is_null($query))
  {
    $rows = $query->execute();
  }
  elseif (isset($model) && !is_null($model))
  {
    $rows = Doctrine_Query::create()->from($model . ' m')->execute();
  }
  elseif (is_null($query) && is_null($model))
  {
    return '';
  }
  $options = array();
  foreach ($rows as $row)
  {
    $row = $row->toArray();
    $k = $row[$key];
    $v = $row[$value];
    $options[$k] = $v;
  }
  return form_dropdown($name, $options, $selected, $extra);
}




// ------------------------------------------------------------------------

/**
 * Checkbox Field
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_checkbox'))
{
	function form_checkbox($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		$defaults = array('type' => 'checkbox', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

		if (is_array($data) AND array_key_exists('checked', $data))
		{
			$checked = $data['checked'];

			if ($checked == FALSE)
			{
				unset($data['checked']);
			}
			else
			{
				$data['checked'] = 'checked';
			}
		}

		if ($checked == TRUE)
		{
			$defaults['checked'] = 'checked';
		}
		else
		{
			unset($defaults['checked']);
		}

		return "<input "._parse_form_attributes($data, $defaults).$extra." />";
	}
}

// ------------------------------------------------------------------------

/**
 * Radio Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	bool
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_radio'))
{
	function form_radio($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		if ( ! is_array($data))
		{	
			$data = array('name' => $data);
		}

		$data['type'] = 'radio';
		return form_checkbox($data, $value, $checked, $extra);
	}
}

// ------------------------------------------------------------------------

/**
 * Submit Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_submit'))
{	
	function form_submit($data = '', $value = '', $extra = '')
	{
		$defaults = array('type' => 'submit', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

		return "<input "._parse_form_attributes($data, $defaults).$extra." />";
	}
}

// ------------------------------------------------------------------------

/**
 * Reset Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_reset'))
{
	function form_reset($data = '', $value = '', $extra = '')
	{
		$defaults = array('type' => 'reset', 'name' => (( ! is_array($data)) ? $data : ''), 'value' => $value);

		return "<input "._parse_form_attributes($data, $defaults).$extra." />";
	}
}

// ------------------------------------------------------------------------

/**
 * Form Button
 *
 * @access	public
 * @param	mixed
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_button'))
{
	function form_button($data = '', $content = '', $extra = '')
	{
		$defaults = array('name' => (( ! is_array($data)) ? $data : ''), 'type' => 'button');

		if ( is_array($data) AND isset($data['content']))
		{
			$content = $data['content'];
			unset($data['content']); // content is not an attribute
		}

		return "<button "._parse_form_attributes($data, $defaults).$extra.">".$content."</button>";
	}
}

// ------------------------------------------------------------------------

/**
 * Form Label Tag
 *
 * @access	public
 * @param	string	The text to appear onscreen
 * @param	string	The id the label applies to
 * @param	string	Additional attributes
 * @return	string
 */
if ( ! function_exists('form_label'))
{
	function form_label($label_text = '', $id = '', $attributes = array())
	{

		$label = '<label';

		if ($id != '')
		{
			 $label .= " for=\"$id\"";
		}

		if (is_array($attributes) AND count($attributes) > 0)
		{
			foreach ($attributes as $key => $val)
			{
				$label .= ' '.$key.'="'.$val.'"';
			}
		}

		$label .= ">$label_text</label>";

		return $label;
	}
}

// ------------------------------------------------------------------------
/**
 * Fieldset Tag
 *
 * Used to produce <fieldset><legend>text</legend>.  To close fieldset
 * use form_fieldset_close()
 *
 * @access	public
 * @param	string	The legend text
 * @param	string	Additional attributes
 * @return	string
 */
if ( ! function_exists('form_fieldset'))
{
	function form_fieldset($legend_text = '', $attributes = array())
	{
		$fieldset = "<fieldset";

		$fieldset .= _attributes_to_string($attributes, FALSE);

		$fieldset .= ">\n";

		if ($legend_text != '')
		{
			$fieldset .= "<legend>$legend_text</legend>\n";
		}

		return $fieldset;
	}
}

// ------------------------------------------------------------------------

/**
 * Fieldset Close Tag
 *
 * @access	public
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_fieldset_close'))
{
	function form_fieldset_close($extra = '')
	{
		return "</fieldset>".$extra;
	}
}

// ------------------------------------------------------------------------

/**
 * Form Close Tag
 *
 * @access	public
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_close'))
{
	function form_close($extra = '')
	{
		return "</form>".$extra;
	}
}

// ------------------------------------------------------------------------

/**
 * Form Prep
 *
 * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
 *
 * @access	public
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_prep'))
{
	function form_prep($str = '', $field_name = '')
	{
		static $prepped_fields = array();
		
		// if the field name is an array we do this recursively
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = form_prep($val);
			}

			return $str;
		}

		if ($str === '')
		{
			return '';
		}

		// we've already prepped a field with this name
		// @todo need to figure out a way to namespace this so
		// that we know the *exact* field and not just one with
		// the same name
		if (isset($prepped_fields[$field_name]))
		{
			return $str;
		}
		
		$str = htmlspecialchars($str);

		// In case htmlspecialchars misses these.
		$str = str_replace(array("'", '"'), array("&#39;", "&quot;"), $str);

		if ($field_name != '')
		{
			$prepped_fields[$field_name] = $str;
		}
		
		return $str;
	}
}

// ------------------------------------------------------------------------

/**
 * Form Value
 *
 * Grabs a value from the POST array for the specified field so you can
 * re-populate an input field or textarea.  If Form Validation
 * is active it retrieves the info from the validation class
 *
 * @access	public
 * @param	string
 * @return	mixed
 */
if ( ! function_exists('set_value'))
{
	function set_value($field = '', $default = '')
	{
		if (FALSE === ($OBJ =& _get_validation_object()))
		{
			if ( ! isset($_POST[$field]))
			{
				return $default;
			}

			return form_prep($_POST[$field], $field);
		}

		return form_prep($OBJ->set_value($field, $default), $field);
	}
}

// ------------------------------------------------------------------------

/**
 * Set Select
 *
 * Let's you set the selected value of a <select> menu via data in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
if ( ! function_exists('set_select'))
{
	function set_select($field = '', $value = '', $default = FALSE)
	{
		$OBJ =& _get_validation_object();

		if ($OBJ === FALSE)
		{
			if ( ! isset($_POST[$field]))
			{
				if (count($_POST) === 0 AND $default == TRUE)
				{
					return ' selected="selected"';
				}
				return '';
			}

			$field = $_POST[$field];

			if (is_array($field))
			{
				if ( ! in_array($value, $field))
				{
					return '';
				}
			}
			else
			{
				if (($field == '' OR $value == '') OR ($field != $value))
				{
					return '';
				}
			}

			return ' selected="selected"';
		}

		return $OBJ->set_select($field, $value, $default);
	}
}

// ------------------------------------------------------------------------

/**
 * Set Checkbox
 *
 * Let's you set the selected value of a checkbox via the value in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
if ( ! function_exists('set_checkbox'))
{
	function set_checkbox($field = '', $value = '', $default = FALSE)
	{
		$OBJ =& _get_validation_object();

		if ($OBJ === FALSE)
		{ 
			if ( ! isset($_POST[$field]))
			{
				if (count($_POST) === 0 AND $default == TRUE)
				{
					return ' checked="checked"';
				}
				return '';
			}

			$field = $_POST[$field];
			
			if (is_array($field))
			{
				if ( ! in_array($value, $field))
				{
					return '';
				}
			}
			else
			{
				if (($field == '' OR $value == '') OR ($field != $value))
				{
					return '';
				}
			}

			return ' checked="checked"';
		}

		return $OBJ->set_checkbox($field, $value, $default);
	}
}

// ------------------------------------------------------------------------

/**
 * Set Radio
 *
 * Let's you set the selected value of a radio field via info in the POST array.
 * If Form Validation is active it retrieves the info from the validation class
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	bool
 * @return	string
 */
if ( ! function_exists('set_radio'))
{
	function set_radio($field = '', $value = '', $default = FALSE)
	{
		$OBJ =& _get_validation_object();

		if ($OBJ === FALSE)
		{
			if ( ! isset($_POST[$field]))
			{
				if (count($_POST) === 0 AND $default == TRUE)
				{
					return ' checked="checked"';
				}
				return '';
			}

			$field = $_POST[$field];
			
			if (is_array($field))
			{
				if ( ! in_array($value, $field))
				{
					return '';
				}
			}
			else
			{
				if (($field == '' OR $value == '') OR ($field != $value))
				{
					return '';
				}
			}

			return ' checked="checked"';
		}

		return $OBJ->set_radio($field, $value, $default);
	}
}

// ------------------------------------------------------------------------

/**
 * Form Error
 *
 * Returns the error for a specific form field.  This is a helper for the
 * form validation class.
 *
 * @access	public
 * @param	string
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('form_error'))
{
	function form_error($field = '', $prefix = '', $suffix = '')
	{
		if (FALSE === ($OBJ =& _get_validation_object()))
		{
			return '';
		}

		return $OBJ->error($field, $prefix, $suffix);
	}
}

// ------------------------------------------------------------------------

/**
 * Validation Error String
 *
 * Returns all the errors associated with a form submission.  This is a helper
 * function for the form validation class.
 *
 * @access	public
 * @param	string
 * @param	string
 * @return	string
 */
if ( ! function_exists('validation_errors'))
{
	function validation_errors($prefix = '', $suffix = '')
	{
		if (FALSE === ($OBJ =& _get_validation_object()))
		{
			return '';
		}

		return $OBJ->error_string($prefix, $suffix);
	}
}

// ------------------------------------------------------------------------

/**
 * Parse the form attributes
 *
 * Helper function used by some of the form helpers
 *
 * @access	private
 * @param	array
 * @param	array
 * @return	string
 */
if ( ! function_exists('_parse_form_attributes'))
{
	function _parse_form_attributes($attributes, $default)
	{
		if (is_array($attributes))
		{
			foreach ($default as $key => $val)
			{
				if (isset($attributes[$key]))
				{
					$default[$key] = $attributes[$key];
					unset($attributes[$key]);
				}
			}

			if (count($attributes) > 0)
			{
				$default = array_merge($default, $attributes);
			}
		}

		$att = '';
		
		foreach ($default as $key => $val)
		{
			if ($key == 'value')
			{
				$val = form_prep($val, $default['name']);
			}

			$att .= $key . '="' . $val . '" ';
		}

		return $att;
	}
}

// ------------------------------------------------------------------------

/**
 * Attributes To String
 *
 * Helper function used by some of the form helpers
 *
 * @access	private
 * @param	mixed
 * @param	bool
 * @return	string
 */
if ( ! function_exists('_attributes_to_string'))
{
	function _attributes_to_string($attributes, $formtag = FALSE)
	{
		if (is_string($attributes) AND strlen($attributes) > 0)
		{
			if ($formtag == TRUE AND strpos($attributes, 'method=') === FALSE)
			{
				$attributes .= ' method="post"';
			}

		return ' '.$attributes;
		}
	
		if (is_object($attributes) AND count($attributes) > 0)
		{
			$attributes = (array)$attributes;
		}

		if (is_array($attributes) AND count($attributes) > 0)
		{
		$atts = '';

		if ( ! isset($attributes['method']) AND $formtag === TRUE)
		{
			$atts .= ' method="post"';
		}

		foreach ($attributes as $key => $val)
		{
			$atts .= ' '.$key.'="'.$val.'"';
		}

		return $atts;
		}
	}
}

// ------------------------------------------------------------------------

/**
 * Validation Object
 *
 * Determines what the form validation class was instantiated as, fetches
 * the object and returns it.
 *
 * @access	private
 * @return	mixed
 */
if ( ! function_exists('_get_validation_object'))
{
	function &_get_validation_object()
	{
		$CI =& get_instance();

		// We set this as a variable since we're returning by reference
		$return = FALSE;

		if ( ! isset($CI->load->_ci_classes) OR  ! isset($CI->load->_ci_classes['form_validation']))
		{
			return $return;
		}

		$object = $CI->load->_ci_classes['form_validation'];

		if ( ! isset($CI->$object) OR ! is_object($CI->$object))
		{
			return $return;
		}

		return $CI->$object;
	}
}


function html_select_date($params)
{

    /* Default values. */
    $prefix          = "";
    $suffix          = "";
    $start_year      = strftime("%Y");
    $end_year        = $start_year;
    $display_days    = true;
    $display_months  = true;
    $display_years   = true;
    $month_format    = "%B";
    /* Write months as numbers by default  GL */
    $month_value_format = "%m";
    $day_format      = "%02d";
    /* Write day values using this format MB */
    $day_value_format = "%d";
    $year_as_text    = false;
    /* Display years in reverse order? Ie. 2000,1999,.... */
    $reverse_years   = false;
    /* Should the select boxes be part of an array when returned from PHP?
       e.g. setting it to "birthday", would create "birthday[Day]",
       "birthday[Month]" & "birthday[Year]". Can be combined with prefix */
    $field_array     = null;
    /* <select size>'s of the different <select> tags.
       If not set, uses default dropdown. */
    $day_size        = null;
    $month_size      = null;
    $year_size       = null;
    /* Unparsed attributes common to *ALL* the <select>/<input> tags.
       An example might be in the template: all_extra ='class ="foo"'. */
    $all_extra       = null;
    /* Separate attributes for the tags. */
    $day_extra       = null;
    $month_extra     = null;
    $year_extra      = null;
    /* Order in which to display the fields.
       "D" -> day, "M" -> month, "Y" -> year. */
    $field_order     = 'MDY';
    /* String printed between the different fields. */
    $field_separator = "\n";
    $time = time();
    $all_empty       = null;
    $day_empty       = null;
    $month_empty     = null;
    $year_empty      = null;
    $extra_attrs     = '';

    foreach ($params as $_key=>$_value) {
        switch ($_key) {
            case 'prefix':
            case 'suffix':
            case 'time':
            case 'start_year':
            case 'end_year':
            case 'month_format':
            case 'day_format':
            case 'day_value_format':
            case 'field_array':
            case 'day_size':
            case 'month_size':
            case 'year_size':
            case 'all_extra':
            case 'day_extra':
            case 'month_extra':
            case 'year_extra':
            case 'field_order':
            case 'field_separator':
            case 'month_value_format':
            case 'month_empty':
            case 'day_empty':
            case 'year_empty':
                $$_key = (string)$_value;
                break;

            case 'all_empty':
                $$_key = (string)$_value;
                $day_empty = $month_empty = $year_empty = $all_empty;
                break;

            case 'display_days':
            case 'display_months':
            case 'display_years':
            case 'year_as_text':
            case 'reverse_years':
                $$_key = (bool)$_value;
                break;

            default:
                if(!is_array($_value)) {
                    $extra_attrs .= ' '.$_key.'="'.escape_special_chars($_value).'"';
                } else {
                    error_log("html_select_date: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (preg_match('!^-\d+$!', $time)) {
        // negative timestamp, use date()
        $time = date('Y-m-d', $time);
    }
    // If $time is not in format yyyy-mm-dd
    if (preg_match('/^(\d{0,4}-\d{0,2}-\d{0,2})/', $time, $found)) {
        $time = $found[1];
    } elseif ($time) {
        // use template_make_timestamp to get an unix timestamp and
        // strftime to make yyyy-mm-dd
        $time = strftime('%Y-%m-%d', make_timestamp($time));
    }
    // Now split this in pieces, which later can be used to set the select
    $time = explode("-", $time);

    // make syntax "+N" or "-N" work with start_year and end_year
    if (preg_match('!^(\+|\-)\s*(\d+)$!', $end_year, $match)) {
        if ($match[1] == '+') {
            $end_year = strftime('%Y') + $match[2];
        } else {
            $end_year = strftime('%Y') - $match[2];
        }
    }
    if (preg_match('!^(\+|\-)\s*(\d+)$!', $start_year, $match)) {
        if ($match[1] == '+') {
            $start_year = strftime('%Y') + $match[2];
        } else {
            $start_year = strftime('%Y') - $match[2];
        }
    }
    if (strlen($time[0]) > 0) {
        if ($start_year > $time[0] && !isset($params['start_year'])) {
            // force start year to include given date if not explicitly set
            $start_year = $time[0];
        }
        if($end_year < $time[0] && !isset($params['end_year'])) {
            // force end year to include given date if not explicitly set
            $end_year = $time[0];
        }
    }

    $field_order = strtoupper($field_order);

    $html_result = $month_result = $day_result = $year_result = "";

    $field_separator_count = -1;
    if ($display_months) {
    	$field_separator_count++;
        $month_names = array();
        $month_values = array();
        if(isset($month_empty)) {
            $month_names[''] = $month_empty;
            $month_values[''] = '';
        }
        for ($i = 1; $i <= 12; $i++) {
            $month_names[$i] = strftime($month_format, mktime(0, 0, 0, $i, 1, 2000));
            $month_values[$i] = strftime($month_value_format, mktime(0, 0, 0, $i, 1, 2000));
        }

        $month_result .= '<select name=';
        if (null !== $field_array){
            $month_result .= '"' . $field_array . '[' . $prefix . 'Month]'. $suffix.'"';
        } else {
            $month_result .= '"' . $prefix . 'Month'. $suffix.'"';
        }
        if (null !== $month_size){
            $month_result .= ' size="' . $month_size . '"';
        }
        if (null !== $month_extra){
            $month_result .= ' ' . $month_extra;
        }
        if (null !== $all_extra){
            $month_result .= ' ' . $all_extra;
        }
        $month_result .= $extra_attrs . '>'."\n";

        $month_result .= html_options(array('output'     => $month_names,
                                                            'values'     => $month_values,
                                                            'selected'   => isset($time[1]) && (int)$time[1] ? strftime($month_value_format, mktime(0, 0, 0, (int)$time[1], 1, 2000)) : '',
                                                            'print_result' => false));
        $month_result .= '</select>';
    }

    if ($display_days) {
    	$field_separator_count++;
        $days = array();
        if (isset($day_empty)) {
            $days[''] = $day_empty;
            $day_values[''] = '';
        }
        for ($i = 1; $i <= 31; $i++) {
            $days[] = sprintf($day_format, $i);
            $day_values[] = sprintf($day_value_format, $i);
        }

        $day_result .= '<select name=';
        if (null !== $field_array){
            $day_result .= '"' . $field_array . '[' . $prefix . 'Day]'. $suffix.'"';
        } else {
            $day_result .= '"' . $prefix . 'Day'. $suffix.'"';
        }
        if (null !== $day_size){
            $day_result .= ' size="' . $day_size . '"';
        }
        if (null !== $all_extra){
            $day_result .= ' ' . $all_extra;
        }
        if (null !== $day_extra){
            $day_result .= ' ' . $day_extra;
        }
        $day_result .= $extra_attrs . '>'."\n";
        $day_result .= html_options(array('output'     => $days,
                                                          'values'     => $day_values,
                                                          'selected'   => isset($time[2]) && (int)$time[2] ? $time[2] : '',
                                                          'print_result' => false));
        $day_result .= '</select>';
    }

    if ($display_years) {
    	$field_separator_count++;
        if (null !== $field_array){
            $year_name = $field_array . '[' . $prefix . 'Year]' . $suffix;
        } else {
            $year_name = $prefix . 'Year'. $suffix;
        }
        if ($year_as_text) {
            $year_result .= '<input type="text" name="' . $year_name . '" value="' . $time[0] . '" size="4" maxlength="4"';
            if (null !== $all_extra){
                $year_result .= ' ' . $all_extra;
            }
            if (null !== $year_extra){
                $year_result .= ' ' . $year_extra;
            }
            $year_result .= ' />';
        } else {
            $years = range((int)$start_year, (int)$end_year);
            if ($reverse_years) {
                rsort($years, SORT_NUMERIC);
            } else {
                sort($years, SORT_NUMERIC);
            }
            $yearvals = $years;
            if(isset($year_empty)) {
                array_unshift($years, $year_empty);
                array_unshift($yearvals, '');
            }
            $year_result .= '<select name="' . $year_name . '"';
            if (null !== $year_size){
                $year_result .= ' size="' . $year_size . '"';
            }
            if (null !== $all_extra){
                $year_result .= ' ' . $all_extra;
            }
            if (null !== $year_extra){
                $year_result .= ' ' . $year_extra;
            }
            $year_result .= $extra_attrs . '>'."\n";
            $year_result .= html_options(array('output' => $years,
                                                               'values' => $yearvals,
                                                               'selected'   => isset($time[0]) && (int)$time[0] ? $time[0] : '',
                                                               'print_result' => false));
            $year_result .= '</select>';
        }
    }

    // Loop thru the field_order field
    for ($i = 0; $i <= 2; $i++){
        $c = substr($field_order, $i, 1);
        switch ($c){
            case 'D':
                $html_result .= $day_result;
                break;

            case 'M':
                $html_result .= $month_result;
                break;

            case 'Y':
                $html_result .= $year_result;
                break;
        }
        // Add the field seperator
        if($i < $field_separator_count) {
            $html_result .= $field_separator;
        }
    }

    return $html_result;
}

/**
 * Template plugin
 * @package Template
 * @subpackage plugins
 */


/**
 * Template {html_options} function plugin
 *
 * Type:     function<br>
 * Name:     html_options<br>
 * Input:<br>
 *           - name       (optional) - string default "select"
 *           - values     (required if no options supplied) - array
 *           - options    (required if no values supplied) - associative array
 *           - selected   (optional) - string default not set
 *           - output     (required if not options supplied) - array
 * Purpose:  Prints the list of <option> tags generated from
 *           the passed parameters
 * @link http://template.php.net/manual/en/language.function.html.options.php {html_image}
 *      (Template online manual)
 * @author Monte Ohrt <monte at ohrt dot com>
 * @param array
 * @param Template
 * @return string
 * @uses template_function_escape_special_chars()
 */
function html_options($params)
{
    
    
    $name = null;
    $values = null;
    $options = null;
    $selected = array();
    $output = null;
    
    $extra = '';
    
    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'name':
                $$_key = (string)$_val;
                break;
            
            case 'options':
                $$_key = (array)$_val;
                break;
                
            case 'values':
            case 'output':
                $$_key = array_values((array)$_val);
                break;

            case 'selected':
                $$_key = array_map('strval', array_values((array)$_val));
                break;
                
            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.escape_special_chars($_val).'"';
                } else {
                    error_log("html_options: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (!isset($options) && !isset($values))
        return ''; /* raise error here? */

    $_html_result = '';

    if (isset($options)) {
        
        foreach ($options as $_key=>$_val)
            $_html_result .= html_options_optoutput($_key, $_val, $selected);

    } else {
        
        foreach ($values as $_i=>$_key) {
            $_val = isset($output[$_i]) ? $output[$_i] : '';
            $_html_result .= html_options_optoutput($_key, $_val, $selected);
        }

    }

    if(!empty($name)) {
        $_html_result = '<select name="' . $name . '"' . $extra . '>' . "\n" . $_html_result . '</select>' . "\n";
    }

    return $_html_result;

}

function html_options_optoutput($key, $value, $selected) {
    if(!is_array($value)) {
        $_html_result = '<option label="' . escape_special_chars($value) . '" value="' .
           escape_special_chars($key) . '"';
        if (in_array((string)$key, $selected))
            $_html_result .= ' selected="selected"';
        $_html_result .= '>' . escape_special_chars($value) . '</option>' . "\n";
    } else {
        $_html_result = html_options_optgroup($key, $value, $selected);
    }
    return $_html_result;
}

function html_options_optgroup($key, $values, $selected) {
    $optgroup_html = '<optgroup label="' . escape_special_chars($key) . '">' . "\n";
    foreach ($values as $key => $value) {
        $optgroup_html .= html_options_optoutput($key, $value, $selected);
    }
    $optgroup_html .= "</optgroup>\n";
    return $optgroup_html;
}

/**
 * escape_special_chars common function
 *
 * Function: template_function_escape_special_chars<br>
 * Purpose:  used by other template functions to escape
 *           special chars except for already escaped ones
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @return string
 */
function escape_special_chars($string)
{
    if(!is_array($string)) {
        $string = preg_replace('!&(#?\w+);!', '%%%TEMPLATE_START%%%\\1%%%TEMPLATE_END%%%', $string);
        $string = htmlspecialchars($string);
        $string = str_replace(array('%%%TEMPLATE_START%%%','%%%TEMPLATE_END%%%'), array('&',';'), $string);
    }
    return $string;
}

/**
 * Function: template_make_timestamp<br>
 * Purpose:  used by other template functions to make a timestamp
 *           from a string.
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @return string
 */
function make_timestamp($string)
{
    if(empty($string)) {
        // use "now":
        $time = time();

    } elseif (preg_match('/^\d{14}$/', $string)) {
        // it is mysql timestamp format of YYYYMMDDHHMMSS?            
        $time = mktime(substr($string, 8, 2),substr($string, 10, 2),substr($string, 12, 2),
                       substr($string, 4, 2),substr($string, 6, 2),substr($string, 0, 4));
        
    } elseif (is_numeric($string)) {
        // it is a numeric string, we handle it as timestamp
        $time = (int)$string;
        
    } else {
        // strtotime should handle it
        $time = strtotime($string);
        if ($time == -1 || $time === false) {
            // strtotime() was not able to parse $string, use "now":
            $time = time();
        }
    }
    return $time;

}

/* vim: set expandtab: */

/* End of file form_helper.php */
/* Location: ./system/helpers/form_helper.php */