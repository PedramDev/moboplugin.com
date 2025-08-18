<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Admin page function
function mobo_core_sync_page() {

    if (isset($_POST['submit'])) {
        // Verify nonce for security
        check_admin_referer('mobo_core_sync_categories');
        
        // Optional: Add an admin notice
        add_action('admin_notices', function() {
            echo '<div class="updated"><p>همگام سازی دسته بندی موبو کور با موفقیت انجام شد!</p></div>';
        });

        $apiFunc = new \MoboCore\ApiFunctions();

        // $categoriesDataJson = $apiFunc->getCategoriesAsJson();
        // $catFunc = new \MoboCore\WooCommerceCategoryManager();
        // $catFunc->addOrUpdateAllCategories($categoriesDataJson);


        // $productsDataJson = $apiFunc->getProductsAsJson();
       
       $productsDataJson=   json_decode(`
        {
  "totalCount": 1201,
  "data": [
    {
      "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
      "stock": 0,
      "price": 128000,
      "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)",
      "caption": "cute flower bear case",
      "comparePrice": null,
      "url": "/c1020",
      "attributes": [
        {
          "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
          "name": "مدل",
          "values": [
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "11"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "11pro"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "11promax"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "12"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "12pro"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "12promax"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "13"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "13pro"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "13promax"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "14pro"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "14promax"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "15promax"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "7plus/8plus"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "airpods 1/2"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "airpods 3"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "airpods pro"
            },
            {
              "id": "76cdf39e-0eff-4d91-915d-b0dcc053c9ef",
              "value": "x/xs"
            }
          ]
        }
      ],
      "images": [
        {
          "id": "04280a28-fa05-422c-afdf-07f74461cc3b",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "1bc2bdd5-f61c-4a6c-9aea-4518060f895a",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "b0905e6a-e6dc-4404-a16c-ac58f40fec29",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "db9464f9-2de6-490a-8abc-7fc871f49b0b",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "fec4fcb6-8039-4fe7-91fd-6c84faa2ba20",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "168c845b-3900-4810-a384-449c0999de25",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "1a18a950-feb0-4129-a882-96e826c36d79",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "8c25c60d-465d-4be3-b8f7-79d29526f5eb",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "c221c16a-ea93-4dd7-a2b1-e0dc2d459c55",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "fab616b1-5176-4b7a-bd55-943646c3ce87",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "0b1e176a-faa7-4487-bb12-981249475765",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "4d5bab23-20a0-4664-82b2-7c2d898bef90",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "aa721200-fe46-4a19-85ba-564af3d8369b",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "fce6985c-7540-4058-bf3c-f2d3975d60a5",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "fe864961-71fe-4cfb-a8a1-b887b8cf1882",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "3d59aad1-0fc5-4420-8dcf-aa97836d706f",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "3f4a24c8-b2eb-4524-8db5-1a75eb1e6435",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "64dc8a98-3bc8-45cf-be28-cd8532feec53",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "894b814e-a317-414d-9e7f-34bad5285ae0",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "8da89c09-aae1-4e0a-8b84-276c90da8009",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "01eb8bce-54c0-460e-a2a9-902b29261e4b",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "15093599-0f65-4796-9ab6-874cd2ca0b88",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "2f3e6342-2a02-44a9-8e67-49a7993d777a",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "5382a20a-9f9a-4cf4-b56a-3198ea44f024",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "6b08dc33-c029-406c-8544-486bc2493353",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "0b47fbdf-b5d3-447f-860b-592c7268e9b3",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "10aa5b7f-8461-47f1-9c96-e74dbd5d6565",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "3dbbacf3-1304-45f6-be61-9eae0ab3c4af",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "5e71d5f3-5111-45c0-bda8-549e0d9f4303",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "b00f49f4-4798-40e1-af65-051cff8cd83c",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "09294f3f-4496-4c0d-997d-4d4189bc8c8b",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "2cbd3203-3546-4e29-b36a-1a4071016990",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "7c26c0f2-a9ef-42bd-b07d-3d672c585e94",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "7eb823af-a8c8-48f0-9b0a-204ae9a3c86c",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "f8a8b6e6-134b-4a17-b462-809901d8691b",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "7bdecbb7-a2c6-4178-823d-10b0ecb13725",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "944f7f0e-66d8-4fbf-9a5c-df92924c4267",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "995b8dda-82de-4da8-8543-b2ea4af8894d",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "edd2837c-77e6-4433-9cb6-e960a75f9d3b",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "f64f9865-862e-454d-97e7-5b612d482da1",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "3de58468-ace8-42b3-80df-8efb99890b48",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "581713c2-62ef-4162-b09a-458732beeb5f",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "5bdff9bb-114c-41dc-97df-8544ccc99901",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "853f70c4-d1fb-4fd8-997e-4f99678298f1",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "ff69aa1d-70c3-4d32-a7d5-bb57593f53b8",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "43c85456-e3e1-4635-bed2-7ce7b5de0746",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "6e944f6d-cfe7-4463-87de-98b713ec3ad2",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "85576b19-9642-4b02-a928-92f66956490c",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "dffbbdd6-9a77-495c-bd8e-596e012104f2",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "eca2727a-fa35-46b5-ae59-011797c3a0c2",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "258dbae6-ed8c-426e-879f-24e815605f13",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "42e47ef2-fe2e-4576-977e-7a1af42f4957",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "6182ffbb-3113-444b-ba1e-86e7d923b5a3",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "a61e0b7b-f1bc-4c08-a86e-f982b63290c8",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "c36fafae-9841-4d45-8d1c-40348b2cfee9",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "19eb0a5c-af08-4e30-8cd1-0291be9edff8",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "70fd4437-21bb-41e3-8da0-4e1e39474aa4",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "a9349503-8dd2-48b3-b670-44b9b0ccc129",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "c0a579cc-6f9d-4ad6-9c33-45dcb32a8acd",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "c5a58afa-3550-4f61-87bf-c5fccf2f6085",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "5aff72da-5448-4b30-b695-701b65e2868d",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "6a5ca903-a052-49b9-aa81-e27bad3764d2",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "7e2c5ed6-aacd-4c46-a4f3-5f415f002741",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "ed134c9a-4776-43f1-aacf-27afbee14921",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "f4bd3dea-0cdd-47bf-b7c7-a6c0414c16a1",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "285cc287-c00f-4058-aec8-fa28a5205e0c",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "344c5200-6b8f-482d-9cf1-80626dec4a22",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "79dcbb60-4376-462f-adf5-542ecf87c4b9",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "baced167-b50d-4a4e-a99d-26dc0e0a2fd2",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "bf078b24-272f-46f9-8847-e89a36e31373",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "063f10ed-9631-48c0-9235-74b115f641b0",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "4f908466-7b1f-41c7-a46f-bf7f2d69581d",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "a66f8fe8-1856-484c-953b-9c9a6d4a5731",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "b9326e23-5513-4176-ae56-df5f1837d712",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "fb866ae2-26c9-4557-84e2-6049fd027c2f",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "36e55a27-959b-44d3-a7ad-0dab94c414d5",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "505fafe5-c463-4497-92d5-e3cb8857c138",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "8ce1d896-4d4f-4754-907f-c5b4950b5e70",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "e25ca069-3507-49c4-85e8-0b9e68181a3e",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "fb6c3273-0a16-4bc0-a2f6-2d50df364c58",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "3ff7e736-8b4f-47fa-92c5-9517bafc7f19",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "50466c8a-c4bf-4b07-84d8-7957df63e8d8",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "82f19de9-2536-4ce7-8803-49c5a3154732",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "a8caad7d-f91a-4774-84c5-c00da30ac205",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "ed29271b-708f-490b-9364-834ec8270f81",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "1241558a-167b-4f9c-8b11-1e186c837333",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "125c504c-ccda-458a-a04a-69555ab63f1e",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "4d632cca-16f1-43ad-991f-2ee8e8f3a87c",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "59b73b20-f89e-4436-b3ce-cb61bc478a25",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "e21ee50d-27fb-4a42-93f9-4b1909a68044",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "4c27f094-4677-45f3-a49c-704ea5ebd2ee",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "6d0e57f8-9756-4e18-975e-8c47d2b6d9db",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "b4abcea0-3539-4f76-bd38-ad71253ec151",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "d2747956-873f-4cb6-a149-170e65181e00",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "fbe4be2f-2d8c-4f19-ba35-5b9bf58f6bb1",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "0cd50963-a429-45ec-b9d9-b63c9f99c283",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "5dcc397e-9535-4ec3-b5a9-4dcc4c42748b",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "606cd59a-f506-4178-9a1c-570758da6f33",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "7dc1ca4f-843c-4f40-98d7-3f0391e27b6b",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "e6945c39-3d8c-44d8-8da2-a603fd20ef9f",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "1966f9ae-5127-4a8f-8dab-4672659b15f0",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "2e7828a5-11ba-4253-bbb5-315deaa438d0",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "bd4751b0-a461-4d15-9c76-aabcafe0bfcd",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "d1cf4580-ee1f-4b53-bb65-7a872bde35ab",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "d4baf85f-ce0d-468c-8436-a8a3b1df7c70",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "058a3e55-136d-43a8-8863-c5020fec272e",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "27e2368c-eef4-4390-8bde-36a5634b7699",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "43a298b9-0933-4bc5-b96b-76d73b399162",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "7319822b-b930-4072-abbe-4b147ef07d86",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "b0de267a-a449-44bb-81a6-b5d7c6762c20",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "26efa764-6005-4a64-ba63-7092942d4f2a",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "3560cf6c-d705-450f-bb15-29c621649694",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "3586da85-4aeb-4683-ab2d-41cfb7843ead",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "807edc96-deb1-4818-98e7-a777abbeb6fb",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "b9a2ddf4-889c-4006-b5a7-1100c5db5ef5",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "516c4737-8f13-4bd9-8d75-f9df6d8701f5",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "5b205321-a9b2-44e1-8c28-4618cb219220",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "6c8c8c1d-592f-4616-99d0-b28c5ae7bb42",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "9f07d32c-bf1f-4f32-9b5e-289401c3613f",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        },
        {
          "id": "9fd4079c-dc8e-48ed-a058-04a477a2aea2",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "51564737-6481-4aac-97b5-35a11b764acc",
          "url": "https://mobomobo.ir/uploads/products/95cbbd.jpg"
        },
        {
          "id": "67699c3e-8e3b-4fed-b759-070f81e381c1",
          "url": "https://mobomobo.ir/uploads/products/ee6c31.jpg"
        },
        {
          "id": "8009973c-1acd-473e-b3aa-1ff7a48aa5cb",
          "url": "https://mobomobo.ir/uploads/products/f15ecf.jpg"
        },
        {
          "id": "c80806a2-e8a0-422b-9433-b3e42c67fdba",
          "url": "https://mobomobo.ir/uploads/products/3d4cd6.jpg"
        },
        {
          "id": "f7fa38a9-119f-4b2a-b3d0-99382b01ce5c",
          "url": "https://mobomobo.ir/uploads/products/679a7c.jpg"
        }
      ],
      "productCategories": [
        {
          "categoryId": "049b59d5-fc5e-4e69-be3e-ed3e2f8b66b9"
        },
        {
          "categoryId": "0e6426d4-7039-485e-93c2-12e5812ab662"
        },
        {
          "categoryId": "529cde38-3ab2-4c33-ae59-45ad0a375f94"
        },
        {
          "categoryId": "609f97c4-2011-4186-8729-e1aa8a798c3a"
        },
        {
          "categoryId": "6ed5fa2b-6be7-45c3-b5ab-9a60d353f060"
        },
        {
          "categoryId": "9814f3f3-288d-4105-b9c4-661be7a403e9"
        },
        {
          "categoryId": "b5890229-a7de-45f1-9eee-de76f0017f86"
        },
        {
          "categoryId": "cb43d6cc-435c-4382-8c6d-e23d3fb7cbc0"
        },
        {
          "categoryId": "e5add36d-d3d8-4b8a-a5e8-a82722c0112c"
        },
        {
          "categoryId": "e753567b-e764-4981-bb0f-2df38414854f"
        },
        {
          "categoryId": "fd3cacd2-86e9-48fc-baf7-f1380342e1ac"
        }
      ],
      "variants": [
        {
          "variantId": "1a03dc6d-f3de-4d76-9e5e-bc88cf1f1644",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 7plus/8plus",
          "attributes": [
            {
              "name": "مدل",
              "option": "7plus/8plus"
            }
          ]
        },
        {
          "variantId": "1f187bea-424c-47f3-bcb4-ffe3b4453b83",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 13",
          "attributes": [
            {
              "name": "مدل",
              "option": "13"
            }
          ]
        },
        {
          "variantId": "2ae413ed-08a2-401b-abd7-c67adda3b3fe",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 12promax",
          "attributes": [
            {
              "name": "مدل",
              "option": "12promax"
            }
          ]
        },
        {
          "variantId": "2af4920c-54f6-4e0a-b839-2a2da3c90f89",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 11",
          "attributes": [
            {
              "name": "مدل",
              "option": "11"
            }
          ]
        },
        {
          "variantId": "2b6dc4b5-ce40-40a0-ac06-fab82fbaa553",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 14pro",
          "attributes": [
            {
              "name": "مدل",
              "option": "14pro"
            }
          ]
        },
        {
          "variantId": "3065663f-9c2f-48ca-a8a6-46740ec89d76",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: airpods 1/2",
          "attributes": [
            {
              "name": "مدل",
              "option": "airpods 1/2"
            }
          ]
        },
        {
          "variantId": "34f97650-bff1-46aa-bfb2-074278146c71",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 14promax",
          "attributes": [
            {
              "name": "مدل",
              "option": "14promax"
            }
          ]
        },
        {
          "variantId": "3a06646f-4279-455f-b734-3939332b4e85",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 12pro",
          "attributes": [
            {
              "name": "مدل",
              "option": "12pro"
            }
          ]
        },
        {
          "variantId": "504e7d69-d66d-429f-a00c-fe963c7e2da0",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 13promax",
          "attributes": [
            {
              "name": "مدل",
              "option": "13promax"
            }
          ]
        },
        {
          "variantId": "5260c2ec-71c1-4bdb-ae28-c90025fc63df",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 11promax",
          "attributes": [
            {
              "name": "مدل",
              "option": "11promax"
            }
          ]
        },
        {
          "variantId": "5468cc07-84d6-43e7-ac78-5cf5aa6b886c",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: x/xs",
          "attributes": [
            {
              "name": "مدل",
              "option": "x/xs"
            }
          ]
        },
        {
          "variantId": "6514de49-3b0f-4b79-bd38-eb81a1e5db28",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 15promax",
          "attributes": [
            {
              "name": "مدل",
              "option": "15promax"
            }
          ]
        },
        {
          "variantId": "8c168f8d-a08c-4140-b52b-ed7d776e531f",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 11pro",
          "attributes": [
            {
              "name": "مدل",
              "option": "11pro"
            }
          ]
        },
        {
          "variantId": "8de656ed-7155-49a4-b300-b4d089892784",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 13pro",
          "attributes": [
            {
              "name": "مدل",
              "option": "13pro"
            }
          ]
        },
        {
          "variantId": "9e64a03a-aebb-42e5-aa52-35f732e0a9e2",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: 12",
          "attributes": [
            {
              "name": "مدل",
              "option": "12"
            }
          ]
        },
        {
          "variantId": "c09ba394-4d9d-40bd-8939-fdfc5243ed09",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: airpods pro",
          "attributes": [
            {
              "name": "مدل",
              "option": "airpods pro"
            }
          ]
        },
        {
          "variantId": "d38d0496-7047-4c36-9173-81d59b7f3e59",
          "price": 128000,
          "comparePrice": null,
          "stock": 0,
          "productId": "94a802b4-9cfc-4c32-b766-666f68089360",
          "title": "قاب خرس گلدار سفید با ایرپاد ست (کدc1020)\r\nمدل: airpods 3",
          "attributes": [
            {
              "name": "مدل",
              "option": "airpods 3"
            }
          ]
        }
      ]
    }
  ],
  "pageNumber": 1,
  "recordPerPage": 1
}`, true);

        // $productsDataJson = json_decode($body, true);
        $productFunc = new \MoboCore\WooCommerceProductManager();
        $productFunc->update_product($productsDataJson);

    }
    ?>
        <form method="post" action="">
            <?php wp_nonce_field('mobo_core_sync_categories'); ?>
            <?php submit_button('همگام سازی دسته بندی'); ?>
        </form>
    <?php
}