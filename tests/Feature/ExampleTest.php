<?php

test('root path redirects to dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
