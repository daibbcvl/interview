<?php

namespace App\Document;

class TeamNode
{
    public $teamName;
    public $parentTeam;
    public $managerName;
    public $businessUnit;
    public $children = [];

    public function __construct($teamName, $parentTeam = null, $managerName = '', $businessUnit = null)
    {
        $this->teamName = $teamName;
        $this->parentTeam = $parentTeam;
        $this->managerName = $managerName;
        $this->businessUnit = $businessUnit;
    }

    public function addChild(TeamNode $child)
    {
        $this->children[$child->teamName] = $child;
    }
}
