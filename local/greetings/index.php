<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_greetings
 * @copyright   2024 Rami SAHRAOUI <sahraoui.rami.1@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/greetings/lib.php');

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url(new moodle_url("/local/greetings/index.php"));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string("pluginname", "local_greetings"));
$PAGE->set_heading(get_string("pluginname", "local_greetings"));

require_login();

if (isguestuser()) {
    throw new moodle_exception('noguest');
}

$allowpost = has_capability('local/greetings:postmessages', $context);
$allowpostview = has_capability('local/greetings:viewmessages', $context);
$deleteanypost = has_capability('local/greetings:deleteanymessages', $context);
$deleteownpost = has_capability('local/greetings:deleteownmessages', $context);
$editanypost = has_capability('local/greetings:editanymessages', $context);
$editownpost = has_capability('local/greetings:editownmessages', $context);

$action = optional_param('action', '', PARAM_TEXT);

if ($action == 'del') {

    require_sesskey();

    $id = required_param('id', PARAM_TEXT);

    $m = $DB->get_record('local_greetings_messages', ['id' => $id]);

    if ($deleteanypost || ($deleteownpost && ($m->userid === $USER->id))) {
        $DB->delete_records('local_greetings_messages', ['id' => $id]);

        redirect($PAGE->url);
    }
}

if ($action == 'edit') {

    require_sesskey();

    $id = required_param('id', PARAM_TEXT);

    $m = $DB->get_record('local_greetings_messages', ['id' => $id]);

    $message = required_param('message', PARAM_TEXT);

    if ($m && !empty($message) &&
            ($editanypost || ($editownpost && ($m->userid === $USER->id)))
    ) {
        $newrecord = new stdClass;
        $newrecord->id = $id;
        $newrecord->message = $message;

        $DB->update_record('local_greetings_messages', $newrecord);

        redirect($PAGE->url);

    }
}

$messageform = new \local_greetings\form\message_form();

echo $OUTPUT->header();

echo '<h3>' . local_greetings_get_greeting($USER) . '</h3>';

if ($allowpost) {
    $messageform->display();
}

$userfields = \core_user\fields::for_name()->with_identity($context);
$userfieldssql = $userfields->get_sql('u');

$sql = "SELECT m.id, m.message, m.timecreated, m.userid {$userfieldssql->selects}
          FROM {local_greetings_messages} m
     LEFT JOIN {user} u ON u.id = m.userid
      ORDER BY timecreated DESC";

$messages = $DB->get_records_sql($sql);
echo $OUTPUT->box_start('card-columns');

if ($allowpostview) {
    foreach ($messages as $m) {
        echo html_writer::start_tag('div', ['class' => 'card']);
        echo html_writer::start_tag('div', ['class' => 'card-body']);
        echo html_writer::tag('p', format_text($m->message, FORMAT_PLAIN), ['class' => 'card-text']);
        echo html_writer::tag(
                'p',
                get_string('postedby', 'local_greetings', $m->firstname),
                ['class' => 'card-text']
        );
        echo html_writer::start_tag('p', ['class' => 'card-text']);
        echo html_writer::tag('small', userdate($m->timecreated), ['class' => 'text-muted']);
        echo html_writer::end_tag('p');
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');

        $candelete = $deleteanypost || ($deleteownpost && ($m->userid === $USER->id));
        $canedit = $editanypost || ($editownpost && ($m->userid === $USER->id));

        if ($candelete || $canedit) {
            echo html_writer::start_tag('p', ['class' => 'card-footer text-center']);
            if ($canedit) {
                echo html_writer::link(
                        new moodle_url(
                                '/local/greetings/index.php',
                                [
                                        'action' => 'edit',
                                        'id' => $m->id,
                                        'message' => "Edited message !!",
                                        'sesskey' => sesskey(),
                                ]
                        ),
                        $OUTPUT->pix_icon('i/edit', get_string('edit')),
                        ['role' => 'button'],
                );
            }
            if ($candelete) {
                echo html_writer::link(
                        new moodle_url(
                                '/local/greetings/index.php',
                                [
                                        'action' => 'del',
                                        'id' => $m->id,
                                        'sesskey' => sesskey(),
                                ]
                        ),
                        $OUTPUT->pix_icon('t/delete', get_string('delete')),
                        ['role' => 'button'],
                );
            }
            echo html_writer::end_tag('p');
        }
    }
}

echo $OUTPUT->box_end();

if ($data = $messageform->get_data()) {
    $message = required_param('message', PARAM_TEXT);

    if (!empty($message)) {
        $record = new stdClass;
        $record->message = $message;
        $record->timecreated = time();
        $record->userid = $USER->id;

        $DB->insert_record('local_greetings_messages', $record);

        redirect($PAGE->url);
    }
}

echo $OUTPUT->footer();
