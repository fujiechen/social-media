<?php

namespace App\Services;

use App\Dtos\AccountDto;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class AccountService
{
    private AccountTypeService $accountTypeService;
    private FileService $fileService;

    public function __construct(FileService $fileService, AccountTypeService $accountTypeService)
    {
        $this->fileService = $fileService;
        $this->accountTypeService = $accountTypeService;
    }

    public function updateOrCreateAccount(AccountDto $dto): Account
    {
        return DB::transaction(function () use ($dto) {
            $profileAvatarFileId = null;
            if ($dto->profileAvatarFile) {
                $profileAvatarFileId = $this->fileService->getOrCreateFile($dto->profileAvatarFile)->id;
            }

            return Account::updateOrCreate([
                'id' => $dto->id
            ], [
                'instruction' => $dto->instruction,
                'contact_id' => $dto->contactId,
                'account_type_id' => $dto->accountTypeId,
                'nickname' => $dto->nickname,
                'account_no' => $dto->accountNo,
                'account_url' => $dto->accountUrl,
                'admin_username' => $dto->adminUsername,
                'admin_password' => $dto->adminPassword,
                'profile_description' => $dto->profileDescription,
                'landing_template_id' => $dto->landingTemplateId,
                'status' => $dto->status,
                'profile_avatar_file_id' => $profileAvatarFileId
            ]);
        });
    }

    public function batchCreateMissingAccountFromContact(): void {
        DB::transaction(function() {
            $contacts = Contact::all();
            /**
             * @var Contact $contact
             */
            foreach ($contacts as $contact) {
                $accountTypes = $this->accountTypeService->fetchAllAccountTypes($contact->type);
                /**
                 * @var AccountType $accountType
                 */
                foreach ($accountTypes as $accountType) {
                    if (Account::query()
                            ->where('contact_id', '=', $contact->id)
                            ->where('account_type_id', '=', $accountType->id)->count() > 0) {
                        continue;
                    }

                    $this->updateOrCreateAccount(new AccountDto([
                        'instruction' => '手动创建账号, 需要挑选或创建promotion template',
                        'contactId' => $contact->id,
                        'accountTypeId' => $accountType->id,
                        'status' => Account::STATUS_DRAFT,
                    ]));
                }
            }
        });
    }
}
