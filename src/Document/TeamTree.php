<?php

namespace App\Document;

class TeamTree
{
    private $nodes = [];
    private $rootNodes = [];

    public function __construct(array $records)
    {
        $this->buildTree($records);
    }

    private function buildTree(array $records)
    {
        foreach ($records as $record) {
            $teamName = $record['team'];
            $parentTeam = $record['parent_team'] ?? null;
            $managerName = $record['manager_name'];
            $businessUnit = $record['business_unit'] ?? null;

            $node = new TeamNode($teamName, $parentTeam, $managerName, $businessUnit);
            $this->nodes[$teamName] = $node;
        }

        // Build hierarchy
        foreach ($this->nodes as $teamName => $node) {
            $parentTeam = $node->parentTeam;
            if ($parentTeam && isset($this->nodes[$parentTeam])) {
                $this->nodes[$parentTeam]->addChild($node);
            } else {
                $this->rootNodes[$teamName] = $node;
            }
        }
    }


    public function printTree()
    {
        return $this->convertToJson($this->rootNodes);
    }

    private function convertToJson($nodes)
    {
        $result = [];
        foreach ($nodes as $node) {
            $result[$node->teamName] = [
                'teamName' => $node->teamName,
                'parentTeam' => $node->parentTeam ?? '',
                'managerName' => $node->managerName,
                'businessUnit' => $node->businessUnit ?? '',
                'teams' => $this->convertToJson($node->children),
            ];
        }
        if (empty($result)) {
            return new \stdClass();
        }

        return $result;
    }

    public function createBranchUpToTeamName($teamName)
    {
        if(empty($teamName)) {
            return $this;
        }
        // Find the node with the given team name
        $targetNode = $this->findNodeByName($teamName);
        if ($targetNode === null) {
            // Node not found, return an empty tree
            return new TeamTree([]);
        }

        // Collect all nodes from the root to the target node
        $branchRecords = $this->collectBranchRecords($targetNode);
        return new TeamTree($branchRecords);
    }

    private function findNodeByName($teamName)
    {
        foreach ($this->rootNodes as $rootNode) {
            $result = $this->dfsFind($rootNode, $teamName);
            if ($result) {
                return $result;
            }
        }
        return null;
    }

    private function dfsFind(TeamNode $node, $teamName)
    {
        if ($node->teamName === $teamName) {
            return $node;
        }

        foreach ($node->children as $child) {
            $result = $this->dfsFind($child, $teamName);
            if ($result) {
                return $result;
            }
        }

        return null;
    }

    private function collectBranchRecords(TeamNode $node)
    {
        $records = [];
        $this->collectRecordsFromRoot($node, $records);
        return $records;
    }

    private function collectRecordsFromRoot(TeamNode $node, &$records)
    {
        // Traverse up from the node to the root and collect records
        $current = $node;
        while ($current !== null) {
            $records[] = [
                'team' => $current->teamName,
                'parent_team' => $current->parentTeam,
                'manager_name' => $current->managerName,
                'business_unit' => $current->businessUnit
            ];
            $current = $this->nodes[$current->parentTeam] ?? null;
        }
        $records = array_reverse($records); // Ensure root-to-leaf order
    }
}
