Feature: Customs Declaration

Scenario: ambil data CD berdasarkan id
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
            details
            """

Scenario: ambil data CD yang idnya gk valid
    When I request "GET /dokumen/cd/91231239131234"
    Then I get "404" response