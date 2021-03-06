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
 * Progress handler that uses a standard Moodle progress bar to display
 * progress. Same as core_backup_display_progress, but the bar does not
 * appear until a certain time has elapsed, and disappears automatically
 * after it finishes.
 *
 * The bar can be re-used, i.e. if you end all sections it will disappear,
 * but if you start all sections, a new bar will be output.
 *
 * @package core_backup
 * @copyright 2013 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_backup_display_progress_if_slow extends core_backup_display_progress {
    /**
     * @var int Waits this many seconds before displaying progress bar
     */
    const DEFAULT_DISPLAY_DELAY = 5;

    /**
     * @var int Number in the next id to use
     */
    private static $nextid = 1;

    /**
     * @var string HTML id for containing div
     */
    protected $id;

    /**
     * @var int Time at which the progress bar should display (if it isn't yet)
     */
    protected $starttime;

    /**
     * Constructs the progress reporter. This will not output HTML just yet,
     * until the required delay time expires.
     *
     * @param int $delay Delay time (default 5 seconds)
     */
    public function __construct($delay = self::DEFAULT_DISPLAY_DELAY) {
        // Set start time based on delay.
        $this->starttime = time() + $delay;
        parent::__construct(false);
    }

    /**
     * Adds a div around the parent display so it can be hidden.
     *
     * @see core_backup_display_progress::start_html()
     */
    public function start_html() {
        $this->id = 'core_backup_display_progress_if_slow' . self::$nextid;
        self::$nextid++;
        echo html_writer::start_div('', array('id' => $this->id));
        parent::start_html();
    }

    /**
     * When progress is updated, after a certain time, starts actually displaying
     * the progress bar.
     *
     * @see core_backup_progress::update_progress()
     */
    public function update_progress() {
        // If we haven't started yet, consider starting.
        if ($this->starttime) {
            if (time() > $this->starttime) {
                $this->starttime = 0;
            } else {
                // Do nothing until start time.
                return;
            }
        }

        // We have started, so handle as default.
        parent::update_progress();
    }

    /**
     * Finishes parent display then closes div and hides it.
     *
     * @see core_backup_display_progress::end_html()
     */
    public function end_html() {
        parent::end_html();
        echo html_writer::end_div();
        echo html_writer::script('document.getElementById("' . $this->id .
                '").style.display = "none"');
    }
}
