<?php

require_once "phing/Task.php";

/**
 * Inline For Task
 * Example:
 *     <for list="${build.projects}" param="build.project">
 *     <do>
 *          <phingcall target="bootstrap.export" />
 *     </do>
 *     </for>
 *     
 * @author alex@easytobook.com
 *
 */
class ForTask extends Task {

	/** Delimiter that separates items in $list */
	private $delimiter = ',';
	
	/** Delimter-separated list of values to process. */
	private $list;
	
	/** Name of parameter to pass to callee */
	private $param;
	/**
	 * @var SequentialTask
	 */
	private $doTasks = null;
	
	/**
	 * A nested <do> element - a container of tasks that will
	 * be run on each itteration
	 *
	 * <p>Not required.</p>
	 */
	public function addDo(SequentialTask $t) {
		if ($this->doTasks != null) {
			throw new BuildException("You must not nest more than one <do> into <fo>");
		}
		$this->doTasks = $t;
	}
	
	public function setList($list) {
		$this->list = (string) $list;
	}
	
	public function setDelimiter($delimiter) {
		$this->delimiter = (string) $delimiter;
	}
	
	public function setParam($param) {
		$this->param = (string) $param;
	}
	
	public function main() {
		if ($this->list === null) {
			throw new BuildException("Need list to iterate through");
		}
		
		$arr = explode($this->delimiter, $this->list);
		
		foreach ($arr as $value) {
			$value = trim($value);
			$this->getProject()->setProperty($this->param, $value);
			
			if ($this->doTasks != null) {
				$this->doTasks->main();
			}
		}
	}
	

}
