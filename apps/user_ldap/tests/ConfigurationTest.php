<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2018-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */
namespace OCA\User_LDAP\Tests;

use OCA\User_LDAP\Configuration;

class ConfigurationTest extends \Test\TestCase {
	protected Configuration $configuration;

	protected function setUp(): void {
		parent::setUp();
		$this->configuration = new Configuration('t01', false);
	}

	public static function configurationDataProvider(): array {
		$inputWithDN = [
			'cn=someUsers,dc=example,dc=org',
			'  ',
			' cn=moreUsers,dc=example,dc=org '
		];
		$expectWithDN = [
			'cn=someUsers,dc=example,dc=org',
			'cn=moreUsers,dc=example,dc=org'
		];

		$inputNames = [
			'  uid  ',
			'cn ',
			' ',
			'',
			' whats my name',
			'	'
		];
		$expectedNames = ['uid', 'cn', 'whats my name'];

		$inputString = ' alea iacta est ';
		$expectedString = 'alea iacta est';

		$inputHomeFolder = [
			' homeDirectory ',
			' attr:homeDirectory ',
			' '
		];

		$expectedHomeFolder = [
			'attr:homeDirectory', 'attr:homeDirectory', ''
		];

		$password = ' such a passw0rd ';

		return [
			'set general base' => ['ldapBase', $inputWithDN, $expectWithDN],
			'set user base' => ['ldapBaseUsers', $inputWithDN, $expectWithDN],
			'set group base' => ['ldapBaseGroups', $inputWithDN, $expectWithDN],

			'set search attributes users' => ['ldapAttributesForUserSearch', $inputNames, $expectedNames],
			'set search attributes groups' => ['ldapAttributesForGroupSearch', $inputNames, $expectedNames],

			'set user filter objectclasses' => ['ldapUserFilterObjectclass', $inputNames, $expectedNames],
			'set user filter groups' => ['ldapUserFilterGroups', $inputNames, $expectedNames],
			'set group filter objectclasses' => ['ldapGroupFilterObjectclass', $inputNames, $expectedNames],
			'set group filter groups' => ['ldapGroupFilterGroups', $inputNames, $expectedNames],
			'set login filter attributes' => ['ldapLoginFilterAttributes', $inputNames, $expectedNames],

			'set agent password' => ['ldapAgentPassword', $password, $password],

			'set home folder, variant 1' => ['homeFolderNamingRule', $inputHomeFolder[0], $expectedHomeFolder[0]],
			'set home folder, variant 2' => ['homeFolderNamingRule', $inputHomeFolder[1], $expectedHomeFolder[1]],
			'set home folder, empty' => ['homeFolderNamingRule', $inputHomeFolder[2], $expectedHomeFolder[2]],

			// default behaviour, one case is enough, special needs must be tested
			// individually
			'set string value' => ['ldapHost', $inputString, $expectedString],

			'set avatar rule, default' => ['ldapUserAvatarRule', 'default', 'default'],
			'set avatar rule, none' => ['ldapUserAvatarRule', 'none', 'none'],
			'set avatar rule, data attribute' => ['ldapUserAvatarRule', 'data:jpegPhoto', 'data:jpegPhoto'],

			'set external storage home attribute' => ['ldapExtStorageHomeAttribute', 'homePath', 'homePath'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('configurationDataProvider')]
	public function testSetValue(string $key, string|array $input, string|array $expected): void {
		$this->configuration->setConfiguration([$key => $input]);
		$this->assertSame($this->configuration->$key, $expected);
	}

	public static function avatarRuleValueProvider(): array {
		return [
			['none', []],
			['data:selfie', ['selfie']],
			['data:sELFie', ['selfie']],
			['data:', ['jpegphoto', 'thumbnailphoto']],
			['default', ['jpegphoto', 'thumbnailphoto']],
			['invalid#', ['jpegphoto', 'thumbnailphoto']],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('avatarRuleValueProvider')]
	public function testGetAvatarAttributes(string $setting, array $expected): void {
		$this->configuration->setConfiguration(['ldapUserAvatarRule' => $setting]);
		$this->assertSame($expected, $this->configuration->getAvatarAttributes());
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('avatarRuleValueProvider')]
	public function testResolveRule(string $setting, array $expected): void {
		$this->configuration->setConfiguration(['ldapUserAvatarRule' => $setting]);
		// so far the only thing that can get resolved :)
		$this->assertSame($expected, $this->configuration->resolveRule('avatar'));
	}
}
