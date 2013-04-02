<?php //$Id: block_profilespecifichtml.php,v 1.2 2012-04-28 10:24:54 vf Exp $

class block_profilespecifichtml extends block_base {

    function init() {
        $this->title = get_string('blockname', 'block_profilespecifichtml');
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('newhtmlblock', 'block_profilespecifichtml'));
    }

    function instance_allow_multiple() {
        return true;
    }

    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = get_context_instance_by_id($this->instance->parentcontextid)) {
            return false;
        }
        //find out if this block is on the profile page
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // this is exception - page is completely private, nobody else may see content there
                // that is why we allow JS here
                return true;
            } else {
                // no JS on public personal pages, it would be a big security issue
                return false;
            }
        }

        return true;
    }

    function get_content() {
    	global $USER, $DB;
    	
        if ($this->content !== NULL) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }
        
        $this->content = new stdClass;

        $this->config->text_all = file_rewrite_pluginfile_urls($this->config->text_all, 'pluginfile.php', $this->context->id, 'block_profilespecifichtml', 'content', NULL);
        $this->content->text = !empty($this->config->text_all) ? format_text($this->config->text_all, FORMAT_HTML, $filteropt) : '';

        if (empty($this->config->field1) && empty($this->config->field2)){
        	$this->content->footer = '';
        	return($this->content);
        }       
        
        $uservalue = $DB->get_field('user_info_data', 'data', array('fieldid' => $this->config->field1, 'userid' => $USER->id)); 
        
        $expr = "\$res1 = {$uservalue} {$this->config->op1} {$this->config->value1} ;";
        @eval($expr);
        
        if ($this->config->op){

	        $uservalue = $DB->get_field('user_info_data', 'data', array('fieldid' => $this->config->field2, 'userid' => $USER->id)); 
	        
	        $expr = "\$res2 = {$uservalue} {$this->config->op2} {$this->config->value2} ;";
	        @eval($expr);
	        
	        $finalexpr = "\$res = $res1 {$this->config->op} $res2 ;"; 
	        @eval($finalexpr);
        } else {
        	$res = @$res1;
        }

		if (@$res){
            $this->config->text_match = file_rewrite_pluginfile_urls($this->config->text_match, 'pluginfile.php', $this->context->id, 'block_profilespecifichtml', 'match', NULL);
        	$this->content->text .= format_text(@$this->config->text_match, FORMAT_HTML, $filteropt);
        } else {
            $this->config->text_nomatch = file_rewrite_pluginfile_urls($this->config->text_nomatch, 'pluginfile.php', $this->context->id, 'block_profilespecifichtml', 'nomatch', NULL);
        	$this->content->text .= format_text(@$this->config->text_nomatch, FORMAT_HTML, $filteropt);
        }
        $this->content->footer = '';

        unset($filteropt); // memory footprint

        return $this->content;
    }

    /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        global $DB;
        
        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match
		// change proposed by jcockrell 
        $config->text_all = file_save_draft_area_files($data->text_all['itemid'], $this->context->id, 'block_profilespecifichtml', 'content', 0, array('subdirs'=>true), $data->text_all['text']);
        $config->format_all = $data->text_all['format'];

        $config->text_match = file_save_draft_area_files($data->text_match['itemid'], $this->context->id, 'block_profilespecifichtml', 'match', 0, array('subdirs'=>true), $data->text_match['text']);
        $config->format_match = $data->text_matched['format'];

        $config->text_nomatch = file_save_draft_area_files($data->text_nomatch['itemid'], $this->context->id, 'block_profilespecifichtml', 'nomatch', 0, array('subdirs'=>true), $data->text_nomatch['text']);
        $config->format_nomatch = $data->text_nomatched['format'];

        parent::instance_config_save($config, $nolongerused);
    }

    /*
     * Hide the title bar when none set..
     */
    function hide_header(){
        return empty($this->config->title);
    }
}
?>
