Feature: Customs Declaration

Scenario: ambil data CD berdasarkan id
    Given I use the token "token_pdtt"
    When I request "GET /dokumen/cd/3"
    Then I get "200" response
    And scope into the "data" property
        And the properties exist:
            """
            id
            no_dok
            tgl_dok
            penumpang
            lokasi
            declare_flags
            jumlah_detail
            """
        And the "jumlah_detail" property is an integer

Scenario: ambil data detail CD
    Given I use the token "token_pdtt"
    When I request "GET /dokumen/cd/3/details"
    Then I get "200" response
        And the properties exist:
            """
            data
            """
        And the "data" property is an array

Scenario: ambil data CD yang idnya gk valid
    Given I use the token "token_pdtt"
    When I request "GET /dokumen/cd/91231239131234"
    Then I get "404" response