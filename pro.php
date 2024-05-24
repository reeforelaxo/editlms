<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Library of functions for uploading a course enrolment methods CSV file.
 *
 * @package    local_trainingrequest
 * @copyright  2020 Eoin Campbell
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// use dml_exception;
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/csvlib.class.php');
// require_once($CFG->dirroot.'/local/requesttraining/classes/datasources.php');


/**
 * Validates and processes files for uploading a course enrolment methods CSV file
 *
 * @copyright  2013 Fr�d�ric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_trainingrequest_pro {

    private $conn;

    /** @var csv_import_reader */
    protected $cir;

    /** @var array CSV columns. */
    protected $columns = array();

    /** @var int line number. */
    protected $linenb = 0;

    // protected $input;

    // protected $next;

    protected $insertUser;
    protected $insertRequest;
    /**
     * Constructor, sets the CSV file reader
     *
     * @param csv_import_reader $cir import reader object
     */
    public function __construct(csv_import_reader $cir) {
        $this->cir = $cir;
        $this->columns = $cir->get_columns();
        $this->validate();
        $this->reset();
        // $this->input = $cir->init();
        // $this->next = $cir->next();
        $this->linenb++;

        $this->conn;
    }

    public function execute($track = null) {
        global $DB;

        if (empty($track)) {
            $track = new local_trainingrequest_track(local_trainingrequest_track::NO_OUTPUT);
        }

        // Initialise the output heading row labels.
        $reportheadings = array('line' => get_string('csvline', 'local_trainingrequest'),
        'email' => get_string('email', 'local_trainingrequest'),
        'name'=> get_string('name','local_trainingrequest'),
        'position'=> get_string('position','local_trainingrequest'),
        // 'requestorname' => get_string('reqname', 'local_requesttraining'),
        // 'businessunit' => get_string('businessunit', 'local_requesttraining'),
        // 'categorytraining' => get_string('categorytraining', 'local_requesttraining'),
        // 'nameschoolcomp' => get_string('nameschoolcomp', 'local_requesttraining'),
        // 'schoolcompaddress' => get_string('schoolcompaddress', 'local_requesttraining'),
        // 'cntactprsondesgnation' => get_string('cntactprsondesgnation', 'local_requesttraining'),
        // 'cntactnmber' => get_string('cntactnmber', 'local_requesttraining'),
        // 'dantforthetraining' => get_string('dat', 'local_requesttraining'),
        // 'schoolcomppin' => get_string('schoolcomppin', 'local_requesttraining'),
        // 'nmberofparticipants' => get_string('nop', 'local_requesttraining'),
        // 'participantprofile' => get_string('pprofile', 'local_requesttraining'),
        // 'addtnalinfo' => get_string('addtnalinfo', 'local_requesttraining'),
            );
        $track->start($reportheadings, true);

        // Trace debug messages.
        $trace = new text_progress_trace();
        $total = 0;
        $created = 0;
        $errors = 0;
        $importCount = 0;

        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        $report = array();
        $messagerow = array();

        // $line = true;

        while ($line = $this->cir->next()) {
                 // $track->flush();
                //  if($line){
                //     $line = false;
                //     continue;
                // }
                // $track->flush();
                 $this->linenb++;
                 $total++;
            
             // Read in and process one data line from the CSV file.
            $data = $this->parse_line($line);
            $email = $data ['email'];
            $name = $data ['name'];
            $position = $data ['position'];

            // $requestorname = $data['requestorname'];
            // $businessunit = $data['businessunit'];
            // $categorytraining = $data['categorytraining'];
            // $nameschoolcomp = $data['nameschoolcomp'];
            // $schoolcompaddress = $data['schoolcompaddress'];
            // $cntactprsondesgnation = $data['cntactprsondesgnation'];
            // $cntactnmber = $data['cntactnmber'];
            // $dantforthetraining = $data['dantforthetraining'];
            // $schoolcomppin = $data['schoolcomppin'];
            // $nmberofparticipants = $data['nmberofparticipants'];
            // $participantprofile = $data['participantprofile'];
            // $addtnalinfo = $data['addtnalinfo'];

            // Add line-specific reporting message strings.
            $messagerow['line'] = $this->linenb;
            $messagerow['email'] = $email;
            $messagerow['name'] = $name;
            $messagerow['posiiton'] = $position;
            // $messagerow['requestorname'] = $requestorname;
            // $messagerow['businessunit'] = $businessunit;
            // $messagerow['categorytraining'] = $categorytraining;
            // $messagerow['nameschoolcomp'] = $nameschoolcomp;
            // $messagerow['schoolcompaddress'] = $schoolcompaddress;
            // $messagerow['cntactprsondesgnation'] = $cntactprsondesgnation;
            // $messagerow['cntactnmber'] = $cntactnmber;
            // $messagerow['dantforthetraining'] = $dantforthetraining;
            // $messagerow['schoolcomppin'] = $schoolcomppin;
            // $messagerow['nmberofparticipants'] = $nmberofparticipants;
            // $messagerow['participantprofile'] = $participantprofile;
            // $messagerow['addtnalinfo'] = $addtnalinfo;
            
            if (! empty($email)) {
                $created++;
                $messagerow['result'] = get_string('invalidmethod', 'local_trainingrequest');
                $track->output($messagerow, true);
                continue;
                
            }

            if (empty($email)) {
                $errors++;
                $messagerow['result'] = get_string('invalidmethod', 'local_trainingrequest');
                $track->output($messagerow, false);
                continue;
            }

        }     
        // $record_to_insert = new stdClass();
        // $record_to_insert->email = $email;
        // $record_to_insert->requestorname = $requestorname;
        // $record_to_insert->businessunit = $businessunit;
        // $record_to_insert->categorytraining = $categorytraining;
        // $record_to_insert->nameschoolcomp = $nameschoolcomp;
        // $record_to_insert->schoolcompaddress = $schoolcompaddress;
        // $record_to_insert->cntactprsondesgnation = $cntactprsondesgnation;
        // $record_to_insert->cntactnmber = $cntactnmber;
        // $record_to_insert->dantforthetraining = $dantforthetraining;
        // $record_to_insert->schoolcomppin = $schoolcomppin;
        // $record_to_insert->nmberofparticipants = $nmberofparticipants;
        // $record_to_insert->participantprofile = $participantprofile;
        // $record_to_insert->addtnalinfo = $addtnalinfo;
        // $DB->insert_record('local_requestform', $record_to_insert, false);
        
        // $DB->insert_record('local_requestform', $data);

        // $headers = $this->columns;
        // $this->input;
        // $fieldnames = array();
        // foreach ($headers as $header) {
        //     $fieldnames[] = $header;
        // }
            
        // // $csvData = array();
        //     while ($rowdata = $this->next); {
        //         $total++;
        //         $track->output($fieldnames, $rowdata);
        $message = array(
            get_string('methodstotal', 'local_trainingrequest', $total),
            get_string('methodscreated', 'local_trainingrequest', $created),
            //get_string('methodsupdated', 'local_requesttraining', $updated),
            //get_string('methodsdeleted', 'local_requesttraining', $deleted),
            get_string('methodserrors', 'local_trainingrequest', $errors)
        );
        
        $track->finish();
        $track->results($message);
    }

     /**
     * Parse a line to return an array(column => value)
     *
     * @param array $line returned by csv_import_reader
     * @return array
     */

    protected function parse_line($line) {
        $data = array();
        foreach ($line as $keynum => $value) {
            if (!isset($this->columns[$keynum])) {
                // This should not happen.
                continue;
            }

            $key = $this->columns[$keynum];
            $data[$key] = $value;
        }
        return $data;
    }

     /**
     * Reset the current process.
     *
     * @return void.
     */

        public function reset() {
        $this->processstarted = false;
        $this->linenb = 0;
        $this->cir->init();
        $this->errors = array();
    }

    /**
     * Validation.
     *
     * @return void
     */
    protected function validate() {
        if (empty($this->columns)) {
            throw new moodle_exception('cannotreadtmpfile', 'error');
        } else if (count($this->columns) < 2) {
            throw new moodle_exception('csvfewcolumns', 'error');
        }
    }
}
