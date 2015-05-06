<?php
/**
	Search Functionality
*/

namespace Edoceo\Imperium;

use Edoceo\Radix\DB\SQL;

class Search
{
	// List of Table to Index
	static $obj_list = array(
		'account_journal' => array(
			'name' => 'Journal Entry',
			'cols' => array('date','note'),
		),
		'base_file' => array(
			'name' => 'File',
			'cols' => array('name','kind'),
		),
		'base_note' => array(
			'name' => 'Note',
			'cols' => array('name','data'),
		),
		'contact' => array(
			'name' => 'Contact',
			'cols' => array('contact','company','title','name','email','phone','url','tags'),
		),
		'contact_address' => array(
			'name' => 'Contact Address',
			'cols' => array('kind','address','city','state'),
		),
		'contact_address' => array(
			'name' => 'Contact Channel',
			'cols' => array('name', 'data'),
		),
		'contact_meta' => array(
			'name' => 'Contact Meta',
			'cols' => array('key', 'val'),
		),
		'invoice' => array(
			'name' => 'Invoice',
			'cols' => array('note','status'),
		),
		'invoice_item' => array(
			'name' => 'Invoice Item',
			'cols' => array('name','note'),
		),
		'workorder' => array(
			'name' => 'Work Order',
			'cols' => array('requester','note','status'),
		),
		'workorder_item' => array(
			'name' => 'Work Order Item',
			'cols' => array('date','kind','name','note'),
		),
	);

	static function init_ts()
	{
		// We Re-Create the Table Each Time :(
		// SQL::query('DROP TABLE full_text');
		// $sql = 'CREATE TABLE full_text ( ';
		// $sql.= ' link_to varchar(32) not null, ';
		// $sql.= ' link_id int not null, ';
		// $sql.= ' name varchar(256), ';
		// $sql.= ' ft text, ';
		// $sql.= ' tv tsvector )';
		// SQL::query($sql);
		// SQL::query('CREATE INDEX full_text_tv_idx ON full_text USING gin(tv)');
	}

	static function update()
	{
		foreach (self::$obj_list as $tab => $tab_spec) {

			// Add ft (Full Text) column
			$sql = 'DELETE FROM full_text WHERE link_to = ?';
			$arg = array($tab);
			SQL::query($sql, $arg);

			// Update Desired Columns & Records to that Field
			$buf = array();
			foreach ($tab_spec['cols'] as $col) {
				$buf[] = " coalesce($col::text,'') ";
			}

			$sql = "INSERT INTO full_text ";
			$sql.= " SELECT '$tab',id, ";
			switch ($tab) {
			case 'contact':
				$sql.= " kind || ': ' || CASE WHEN kind = 'Person' THEN contact ELSE company END, ";
				break;
			default:
				$sql.= " '" . $tab_spec['name'] . " #' || id,";
			}
			$sql.= ' (' . implode(" || ' ' || ", $buf) . '), ';
			$sql.= 'to_tsvector(' . implode(" || ' ' || ",$buf) . ')';
			$sql.= " FROM $tab";

			// echo "$sql\n";
			SQL::query($sql);
		}
	}
}