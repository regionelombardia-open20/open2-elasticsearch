<?php

namespace open20\elasticsearch\utilities\classes;

class RtfText extends RtfElement
{
	public $text;
 
	public function dump($level)
	{
		echo "<div style='color:red'>";
		$this->Indent($level);
		echo "TEXT {$this->text}";
		echo "</div>";
	}	
}
