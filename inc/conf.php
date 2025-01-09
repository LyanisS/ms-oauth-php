<?php 
require_once(dirname(__FILE__) . '/functions.php');
prevent_direct_access();

define('MS_TENANT_ID', 'common');

define('MS_CLIENT_ID', '');
define('MS_CLIENT_SECRET', '');

define('MS_REDIRECT_URI', '');

// https://learn.microsoft.com/en-us/graph/permissions-reference
define('MS_SCOPES', ['user.read']);

// define('MS_DOMAIN_HINT', '');

// https://learn.microsoft.com/en-us/graph/api/resources/user?view=graph-rest-1.0#properties
define('MS_USER_ATTRIBUTES', [
    'aboutMe',
    'accountEnabled',
    'ageGroup',
    'assignedLicenses',
    'assignedPlans',
    'birthday',
    'businessPhones',
    'city',
    'companyName',
    'consentProvidedForMinor',
    'country',
    'createdDateTime',
    'creationType',
    // 'customSecurityAttributes', // Required scope - CustomSecAttributeAssignment.Read.All
    'deletedDateTime',
    'department',
    'displayName',
    'employeeHireDate',
    // 'employeeLeaveDateTime', // Required scope - User-LifeCycleInfo.Read.All
    'employeeId',
    'employeeOrgData',
    'employeeType',
    'externalUserState',
    'externalUserStateChangeDateTime',
    'faxNumber',
    'givenName',
    'hireDate',
    'id',
    'identities',
    'imAddresses',
    'interests',
    'isResourceAccount',
    'jobTitle',
    'lastPasswordChangeDateTime',
    'legalAgeGroupClassification',
    'licenseAssignmentStates',
    'mail',
    // 'mailboxSettings', // Required scope - ?
    'mailNickname',
    'mobilePhone',
    'mySite',
    'officeLocation',
    'onPremisesDistinguishedName',
    'onPremisesDomainName',
    'onPremisesExtensionAttributes',
    'onPremisesImmutableId',
    'onPremisesLastSyncDateTime',
    'onPremisesProvisioningErrors',
    'onPremisesSamAccountName',
    'onPremisesSecurityIdentifier',
    'onPremisesSyncEnabled',
    'onPremisesUserPrincipalName',
    'otherMails',
    'passwordPolicies',
    'passwordProfile',
    'pastProjects',
    'postalCode',
    'preferredDataLocation',
    'preferredLanguage',
    'preferredName',
    'provisionedPlans',
    'proxyAddresses',
    'refreshTokensValidFromDateTime',
    'responsibilities',
    'serviceProvisioningErrors',
    'schools',
    'securityIdentifier',
    'showInAddressList',
    // 'signInActivity', // Required scope - AuditLog.Read.All
    'signInSessionsValidFromDateTime',
    'skills',
    'state',
    'streetAddress',
    'surname',
    'usageLocation',
    'userPrincipalName',
    'userType',
]);