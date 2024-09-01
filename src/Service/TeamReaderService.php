<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class TeamReaderService
{
    /**
     * Process the uploaded CSV file and validate its content.
     *
     * @param UploadedFile $file The uploaded CSV file.
     * @return array An array containing 'teams' and 'errors'.
     */
    public function processCsvFile(UploadedFile $file): array
    {
        $roots = [];
        $teams = [];

        try {
            $handle = fopen($file->getPathname(), 'r');

            if ($handle === false) {
                throw new \Exception('Unable to open the file.');
            }

            $header = true;
            $lineNumber = 1;

            while (($row = fgetcsv($handle)) !== false) {
                if ($header) {
                    $header = false;
                    $lineNumber++;
                    continue;
                }

                $team = $this->createTeamFromRecord($row);
                $teams[$lineNumber] = $team;

                if (empty($row[1])) {
                    $roots[] = $lineNumber;
                }

                $lineNumber++;
            }

            fclose($handle);
        } catch (\Exception $e) {
            return [
                'teams' => [],
                'errors' => ['message' => 'Error processing CSV file: ' . $e->getMessage()]
            ];
        }

        return $this->validateTeams($teams, $roots);
    }

    /**
     * Convert a CSV record into a team associative array.
     *
     * @param array $record The CSV record.
     * @return array The team associative array.
     */
    private function createTeamFromRecord(array $record): array
    {
        return [
            'team' => $record[0] ?? null,
            'parent_team' => $record[1] ?? null,
            'manager_name' => $record[2] ?? null,
            'business_unit' => $record[3] ?? null,
        ];
    }

    /**
     * Validate the teams data and ensure there is exactly one root team.
     *
     * @param array $teams The teams data.
     * @param array $roots The list of root lines.
     * @return array The validation result containing 'teams' and 'errors'.
     */
    private function validateTeams(array $teams, array $roots): array
    {
        $errors = [];
        $validationErrors = [];

        if (count($roots) > 1) {
            $errors['message'] = 'Root teams are violated: more than one root node found on lines: ' . implode(', ', $roots);
        } elseif (count($roots) === 0) {
            $errors['message'] = 'No root team found';
        } else {
            foreach ($teams as $line => $team) {
                $error = $this->validateData($team, $line, $line === $roots[0]);
                if (!empty($error)) {
                    $validationErrors[] = $error;
                }
            }

            if (!empty($validationErrors)) {
                $errors['message'] = 'Please fix the line errors above and try again';
                $errors['errors'] = $validationErrors;
            }
        }

        return [
            'teams' => count($errors) === 0 ? $teams : [],
            'errors' => $errors
        ];
    }

    /**
     * Validate a single team's data.
     *
     * @param array $record The team data.
     * @param int $line The line number in the CSV file.
     * @param bool $isRoot Whether the team is a root team.
     * @return array An array of validation errors.
     */
    private function validateData(array $record, int $line, bool $isRoot): array
    {
        $errors = [];

        if (empty($record['team'])) {
            $errors['team'] = "Team name is required";
        }

        if (!$isRoot && empty($record['parent_team'])) {
            $errors['parent_team'] = "Parent team is required";
        }

        if (empty($record['manager_name'])) {
            $errors['manager_name'] = "Manager name is required";
        }

        if (!empty($errors)) {
            $errors['line'] = $line;
        }

        return $errors;
    }
}
