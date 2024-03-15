<?php

return [
    "container" => "templates/Container.html",
    "componet_path" => [
        "Input" => "templates/InputText.html",
        "TextArea" => "templates/InputText.html",
        "Select" => "templates/InputText.html"
    ],
    "resource" => [
        "create" => [
            "stub" => __DIR__ . '/../stub/create.stub',
            "output" => "resources/views"
        ],
        "show" => [
            "stub" => __DIR__ . '/../stub/show.stub',
            "output" => "resources/views"
        ]
    ]
];
