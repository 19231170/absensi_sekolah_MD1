<?php

return array(

    'cache'      => array(
        'memory' => array(
            'driver' => 'memory'
        ),
        'apc'    => array(
            'driver' => 'apc'
        ),
    ),

    'properties' => array(
        'creator'        => 'Absensi QR System',
        'lastModifiedBy' => 'Absensi QR System',
        'title'          => 'Spreadsheet',
        'description'    => 'Excel Document',
        'subject'        => 'Excel Document',
        'keywords'       => 'excel,export,import',
        'category'       => 'Excel',
        'manager'        => 'Admin',
        'company'        => 'Sekolah',
    ),

    'sheets'     => array(
        'auto_filter'             => true,
        'auto_filter_on_merged'   => false,
        'calculate'               => true,
        'concatenate_charts'      => false,
        'include_charts'          => true,
    ),

    'import'     => array(
        'ignore_empty'            => false,
        'dates_as_values'         => false,
        'date_format'             => 'Y-m-d',
        'force_sheets_collection' => false,
        'heading'                 => 'slugged',
        'encoding'                => array(
            'input'  => 'UTF-8',
            'output' => 'UTF-8'
        ),
        'csv'                     => array(
            'delimiter'   => ',',
            'enclosure'   => '"',
            'line_ending' => "\r\n",
            'input_encoding' => 'UTF-8',
        ),
    ),

    'export'     => array(
        'dates_as_values'    => false,
        'date_format'        => 'Y-m-d',
        'autosize'           => true,
        'merge_cells'        => true,
        'force_sheets_collection' => false,
        'encoding'           => array(
            'input'  => 'UTF-8',
            'output' => 'UTF-8'
        ),
        'csv'                => array(
            'delimiter'   => ',',
            'enclosure'   => '"',
            'line_ending' => "\n",
            'use_bom'     => false
        ),
    ),

    'drivers'    => array(
        'PHPExcel' => array(
            'methods' => array(
                'setLocale'                       => 'setLocale',
                'getSheetByCodeName'              => 'getSheetByCodeName',
                'getSheetByNameOrThrow'           => 'getSheetByNameOrThrow',
                'getSheetByName'                  => 'getSheetByName',
                'getSheetCount'                   => 'getSheetCount',
                'getSheetNames'                   => 'getSheetNames',
                'getActiveSheetIndex'             => 'getActiveSheetIndex',
                'getAllSheets'                    => 'getAllSheets',
                'getActiveSheet'                  => 'getActiveSheet',
                'getSheetByIndex'                 => 'getSheetByIndex',
                'setSheetIndex'                   => 'setSheetIndex',
                'setActiveSheetIndex'             => 'setActiveSheetIndex',
                'createSheet'                     => 'createSheet',
                'removeSheetByIndex'              => 'removeSheetByIndex',
                'getSheetCodeName'                => 'getSheetCodeName',
                'setSheetCodeName'                => 'setSheetCodeName',
                'getSheetTitle'                   => 'getTitle',
                'getDefaultStyle'                 => 'getDefaultStyle',
                'setDefaultStyle'                 => 'setDefaultStyle',
                'getActiveSheetStyleArray'        => 'getActiveSheetStyleArray',
                'getSheetProperties'              => 'getSheetProperties',
                'setSelectedCells'                => 'setSelectedCells',
                'getSelectedCells'                => 'getSelectedCells',
                'setFreezePaneByColumnAndRow'     => 'setFreezePaneByColumnAndRow',
                'freezePane'                      => 'freezePane',
                'freezePaneByColumnAndRow'        => 'freezePaneByColumnAndRow',
                'unfreezePane'                    => 'unfreezePane',
                'createView'                      => 'createView'
            )
        )
    ),
);
