<?php

//
//  Copyright (C) 2016 by Jackie Ng
//
//  This library is free software; you can redistribute it and/or
//  modify it under the terms of version 2.1 of the GNU Lesser
//  General Public License as published by the Free Software Foundation.
//
//  This library is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//  Lesser General Public License for more details.
//
//  You should have received a copy of the GNU Lesser General Public
//  License along with this library; if not, write to the Free Software
//  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
//

require_once dirname(__FILE__)."/IntegrationTest.php";
require_once dirname(__FILE__)."/Config.php";

class RestPublishingTests extends IntegrationTest {
    private $anonymousSessionId;
    private $wfsSessionId;
    private $wmsSessionId;
    private $authorSessionId;
    private $adminSessionId;
    private $user1SessionId;
    private $user2SessionId;

    protected function setUp() {
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), "Anonymous", "");
        $this->assertStatusCodeIsNot(401, $resp);
        $this->anonymousSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertStatusCodeIsNot(401, $resp);
        $this->adminSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertStatusCodeIsNot(401, $resp);
        $this->wfsSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertStatusCodeIsNot(401, $resp);
        $this->wmsSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertStatusCodeIsNot(401, $resp);
        $this->authorSessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertStatusCodeIsNot(401, $resp);
        $this->user1SessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/session.json", "POST", array(), $login->user, $login->pass);
        $this->assertStatusCodeIsNot(401, $resp);
        $this->user2SessionId = json_decode($resp->getContent(), true)["PrimitiveValue"]["Value"];
    }
    protected function tearDown() {
        $resp = $this->apiTest("/session/".$this->anonymousSessionId, "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->anonymousSessionId = null;
        $resp = $this->apiTest("/session/".$this->adminSessionId, "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->adminSessionId = null;
        $resp = $this->apiTest("/session/".$this->wfsSessionId, "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->wfsSessionId = null;
        $resp = $this->apiTest("/session/".$this->wmsSessionId, "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->wmsSessionId = null;
        $resp = $this->apiTest("/session/".$this->authorSessionId, "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->authorSessionId = null;
        $resp = $this->apiTest("/session/".$this->user1SessionId, "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->user1SessionId = null;
        $resp = $this->apiTest("/session/".$this->user2SessionId, "DELETE", null);
        $this->assertStatusCodeIs(200, $resp);
        $this->user2SessionId = null;
    }
    public function testHasIds() {
        $this->assertNotNull($this->anonymousSessionId);
        $this->assertNotNull($this->wfsSessionId);
        $this->assertNotNull($this->wmsSessionId);
        $this->assertNotNull($this->authorSessionId);
        $this->assertNotNull($this->adminSessionId);
        $this->assertNotNull($this->user1SessionId);
        $this->assertNotNull($this->user2SessionId);
    }

    private function createInsertPayload($extension, $text, $geom, $session = null) {
        switch ($extension) {
            case "xml":
                return $this->createInsertXml($text, $geom, $session);
            case "json":
                return $this->createInsertJson($text, $geom, $session);
        }
    }

    private function createUpdatePayload($extension, $filter, $text, $geom, $session = null) {
        switch ($extension) {
            case "xml":
                return $this->createUpdateXml($filter, $text, $geom, $session);
            case "json":
                return $this->createUpdateJson($filter, $text, $geom, $session);
        }
    }

    private function createInsertXml($text, $geom, $session = null) {
        $xml = "<FeatureSet>";
        if ($session != null && $session != "") {
            $xml .= "<SessionID>" . $session . "</SessionID>";
        }
        $xml .= "<Features><Feature>";
        $xml .= "<Property><Name>RNAME</Name><Value>" . $text . "</Value></Property>";
        $xml .= "<Property><Name>SHPGEOM</Name><Value>" . $geom . "</Value></Property>";
        $xml .= "</Feature></Features></FeatureSet>";
        return $xml;
    }
    private function createUpdateXml($filter, $text, $geom, $session = null) {
        $xml = "<UpdateOperation>";
        if ($session != null && $session != "") {
            $xml .= "<SessionID>" . $session . "</SessionID>";
        }
        if ($filter != null && $filter != "") {
            $xml .= "<Filter>" . $filter . "</Filter>";
        }
        $xml .= "<UpdateProperties>";
        $xml .= "<Property><Name>RNAME</Name><Value>" . $text . "</Value></Property>";
        $xml .= "<Property><Name>SHPGEOM</Name><Value>" . $geom . "</Value></Property>";
        $xml .= "</UpdateProperties>";
        $xml .= "</UpdateOperation>";
        return $xml;
    }
    private function createInsertJson($text, $geom, $session = null) {
        $sessionPart = "";
        if ($session != null && $session != "") {
            $sessionPart = "\"SessionID\": \"$session\",\n";
        }
        $json = "{
            \"FeatureSet\": {
                $sessionPart
                \"Features\": {
                    \"Feature\": [
                        { 
                            \"Property\": [
                                { \"Name\": \"RNAME\", \"Value\": \"$text\" },
                                { \"Name\": \"SHPGEOM\", \"Value\": \"$geom\" }
                            ] 
                        }
                    ]
                }
            }
        }";
        return $json;
    }

    private function createUpdateJson($filter, $text, $geom, $session = null) {
        $sessionPart = "";
        if ($session != null && $session != "") {
            $sessionPart = "\"SessionID\": \"$session\",\n";
        }
        $filterPart = "";
        if ($filter != null && $filter != "") {
            $filterPart = "\"Filter\": \"$filter\",\n";
        }
        $json = "{
            \"UpdateOperation\": {
                $sessionPart
                $filterPart
                \"UpdateProperties\": {
                    \"Property\": [
                        { \"Name\": \"RNAME\", \"Value\": \"$text\" },
                        { \"Name\": \"SHPGEOM\", \"Value\": \"$geom\" }
                    ] 
                }
            }
        }";
        return $json;
    }

    public function testACLAnonymousXml() {
        $this->__testACLAnonymous(array(42, 43, 1234), "xml", "anonymous", Configuration::MIME_XML);
    }

    public function testACLAnonymousJson() {
        $this->__testACLAnonymous(array(47, 48, 2345), "json", "anonymous", Configuration::MIME_JSON);
    }

    private function assertContentKind($resp, $extension) {
        switch ($extension) {
            case "xml":
                $this->assertXmlContent($resp);
                break;
        }
    }

    private function getExpectedStatusCodeForSession($username, $session) {
        switch (strtolower($username)) {
            case "anonymous":
                return $this->anonymousSessionId === $session ? 200 : 403;
            case "wfsuser":
            	return $this->wfsSessionId === $session ? 200 : 403;
            case "wmsuser":
                return $this->wmsSessionId === $session ? 200 : 403;
            case "author":
                return $this->authorSessionId === $session ? 200 : 403;
            case "administrator":
                return $this->adminSessionId === $session ? 200 : 403;
            case "user1":
       	        return $this->user1SessionId === $session ? 200 : 403;
            case "user2":
                return $this->user2SessionId === $session ? 200 : 403;
        }
    }

    private function getExpectedStatusCodeForLogin($username, $login) {
        if (is_string($login))
            return (strtolower($username) === strtolower($login)) ? 200 : 403;
        else
            return (strtolower($username) === strtolower($login->user)) ? 200 : 403;
    }
    
    private function __testACLAnonymous($testIds, $extension, $username, $mimeType) {
        $testID1 = $testIds[0];
        $testID2 = $testIds[1];
        $testID3 = $testIds[2];

        //With credentials
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "GET", array(), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAnonLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //With session ids
        $resp = $this->apiTest("/data/test_$username/.$extension", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->anonymousSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "GET", array("session" => $this->wfsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wfsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "GET", array("session" => $this->wmsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wmsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "GET", array("session" => $this->authorSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->authorSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->adminSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "GET", array("session" => $this->user1SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user1SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "GET", array("session" => $this->user2SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user2SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Single access - Credentials
        $resp = $this->apiTestWithCredentials("/data/test_$username/" . $testID3. ".$extension", "GET", array(), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAnonLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/" . $testID3. ".$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/" . $testID3. ".$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/" . $testID3. ".$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/" . $testID3. ".$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/" . $testID3. ".$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/" . $testID3. ".$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/" . $testID3. ".$extension", "GET", array(), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Single access - Session ID
        $resp = $this->apiTest("/data/test_$username/" . $testID3. ".$extension", "GET", array("session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->anonymousSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/" . $testID3. ".$extension", "GET", array("session" => $this->wfsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wfsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/" . $testID3. ".$extension", "GET", array("session" => $this->wmsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wmsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/" . $testID3. ".$extension", "GET", array("session" => $this->authorSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->authorSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/" . $testID3. ".$extension", "GET", array("session" => $this->adminSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->adminSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/" . $testID3. ".$extension", "GET", array("session" => $this->user1SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user1SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/" . $testID3. ".$extension", "GET", array("session" => $this->user2SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user2SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Insert - Credentials
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAnonLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Insert - Session ID
        $resp = $this->apiTest("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "anonymous session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->anonymousSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->anonymousSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "wfsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wfsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wfsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "wmsuser session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wmsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wmsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "author session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->authorSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->authorSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "admin session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->adminSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->adminSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "user1 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user1SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user1SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "POST", $this->createInsertPayload($extension, "user2 session", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user2SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user2SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Update - Credentials
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAnonLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "POST", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Update - Session ID
        $resp = $this->apiTest("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->anonymousSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->anonymousSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->adminSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->adminSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wfsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wfsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wmsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wmsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->authorSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->authorSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "PUT", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user1SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user1SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "POST", $this->createUpdatePayload($extension, "Autogenerated_SDF_ID = " . $testID1, "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user2SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user2SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Update - Single Access - Credentials
        $resp = $this->apiTestWithCredentials("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "invalid credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAnonLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/$testID2.$extension", "POST", $this->createUpdatePayload($extension, "", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Update - Single Access - Session ID
        $resp = $this->apiTest("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "anonymous credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->anonymousSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->anonymousSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "admin credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->adminSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->adminSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "wfsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wfsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wfsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "wmsuser credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->wmsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wmsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "author credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->authorSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->authorSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "user1 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user1SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user1SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/$testID2.$extension", "PUT", $this->createUpdatePayload($extension, "", "user2 credentials", "POLYGON ((30 10, 40 40, 20 40, 10 20, 30 10))", $this->user2SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user2SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Delete - Credentials
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), "Foo", "Bar");
        $this->assertStatusCodeIs(401, $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAnonLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAdminLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWfsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getWmsLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getAuthorLogin();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser1Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $login = Configuration::getUser2Login();
        $resp = $this->apiTestWithCredentials("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID1"), $login->user, $login->pass);
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForLogin($username, $login), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Delete - Session ID
        $resp = $this->apiTest("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->anonymousSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->anonymousSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->adminSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->adminSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->wfsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wfsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->wmsSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->wmsSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->authorSessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->authorSessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->user1SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user1SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        $resp = $this->apiTest("/data/test_$username/.$extension", "DELETE", array("filter" => "Autogenerated_SDF_ID = $testID2", "session" => $this->user2SessionId));
        $this->assertStatusCodeIs($this->getExpectedStatusCodeForSession($username, $this->user2SessionId), $resp);
        $this->assertContentKind($resp, $extension);
        $this->assertMimeType($mimeType, $resp);

        //Delete - single access - credentials
        //Delete - single access - Session ID
    }
}

?>