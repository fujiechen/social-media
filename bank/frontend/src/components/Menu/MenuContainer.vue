<script setup>
import PurchaseDetail from '@/components/Menu/Asset/Info/PurchaseDetail.vue';
import UserProductReturns from '@/components/Menu/UserProductReturns.vue';
import OrderPurchase from '@/components/Menu/OrderPurchase.vue';
import Deposit from '@/components/Menu/Asset/Deposit.vue';
import Withdrawal from '@/components/Menu/Asset/Withdrawal.vue';
import Exchange from '@/components/Menu/Asset/Exchange.vue';
import FriendTransfer from '@/components/Menu/Asset/FriendTransfer.vue';
import WithdrawalDetail from '@/components/Menu/Asset/Info/WithdrawalDetail.vue';
import DepositDetail from '@/components/Menu/Asset/Info/DepositDetail.vue';
import TransferDetail from '@/components/Menu/Asset/Info/TransferDetail.vue';
import ExchangeDetail from '@/components/Menu/Asset/Info/ExchangeDetail.vue';
import ContactSupport from '@/components/Menu/Account/ContactSupport.vue';
import CreateAddress from '@/components/Menu/Account/CreateAddress.vue';
import DeleteAddress from '@/components/Menu/Account/DeleteAddress.vue';
import CreateBankAccount from '@/components/Menu/Account/CreateBankAccount.vue';
import DeleteBankAccount from '@/components/Menu/Account/DeleteBankAccount.vue';
import AssetAccountSelect from '@/components/Menu/AssetAccountSelect.vue';
</script>

<template>
  <!-- Invest User Product Returns -->
  <UserProductReturns/>

  <!-- Invest User Order Purchase -->
  <OrderPurchase/>

  <!-- Asset -->
  <AssetAccountSelect/>
  <FriendTransfer/>
  <Deposit/>
  <Withdrawal/>
  <Exchange/>

  <!-- Asset Detail -->
  <WithdrawalDetail/>
  <DepositDetail/>
  <TransferDetail/>
  <ExchangeDetail/>
  <PurchaseDetail/>

  <!-- Account -->
  <ContactSupport/>
  <CreateAddress/>
  <DeleteAddress/>
  <CreateBankAccount/>
  <DeleteBankAccount/>
</template>
