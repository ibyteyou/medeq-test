<template>
  <div id="bill-form">
    <v-form v-model="valid">
      <v-container>
        <v-row>
          <v-col cols="12" md="3">
            <v-text-field
              v-model="form.providerName"
              label="Наименование поставщика"
              required
              hide-details
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="3">
            <v-text-field
              v-model="form.providerInn"
              label="ИНН поставщика"
              hide-details
              required
              @keypress="isNumber($event)"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="3">
            <v-text-field
              v-model="form.providerKpp"
              label="КПП поставщика"
              hide-details
              required
              @keypress="isNumber($event)"
            ></v-text-field>
          </v-col>
          <v-col cols="12" md="3">
            <v-text-field
              v-model="form.providerAddress"
              label="Адрес поставщика"
              hide-details
              required
            ></v-text-field>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12" md="3">
            <v-file-input
              accept="image/png, image/jpeg, image/bmp"
              placeholder="Лого компании"
              prepend-icon="mdi-camera"
              label="Лого компании"
              @update:modelValue="updateLogo"
            ></v-file-input>
          </v-col>
          <v-col cols="12" md="3">
            <v-text-field
              v-model="form.consumerName"
              label="ФИО покупателя"
              required
              hide-details
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model="form.consumerInn"
              label="ИНН покупателя"
              hide-details
              required
              @keypress="isNumber($event)"
            ></v-text-field>
          </v-col>

          <v-col cols="12" md="3">
            <v-text-field
              v-model="form.consumerAddress"
              label="Адрес покупателя"
              hide-details
              required
            ></v-text-field>
          </v-col>
        </v-row>
        <v-table>
          <thead>
            <tr>
              <th class="text-center">
                №
              </th>
              <th class="text-center">
                Товары (работы, услуги)
              </th>
              <th class="text-center">
                Кол-во
              </th>
              <th class="text-center">
                Ед.
              </th>
              <th class="text-center">
                Цена
              </th>
              <th class="text-center">
                Сумма
              </th>
              <th class="text-center">
                Действия
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, key) in form.goods"
              :key="item.name"
            >
              <td>{{ key + 1 }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.count }}</td>
              <td>{{ item.qty }}</td>
              <td>{{ item.price }}</td>
              <td>{{ item.count * item.price }}</td>
              <td>
                <v-btn @click="removeItem(key)">
                  <v-icon>mdi-close</v-icon>
                </v-btn>
              </td>
            </tr>
            <tr>
              <td>
                {{ form.goods.length + 1 }}
              </td>
              <td>
                <v-text-field
                  v-model="newItem.name"
                  label="Наименование товара"
                  hide-details
                  required
                ></v-text-field>
              </td>
              <td>
                <v-text-field
                  v-model="newItem.count"
                  label="Количество"
                  type="number"
                  hide-details
                  required
                  @keypress="isNumber($event)"
                ></v-text-field>
              </td>
              <td>
                <v-text-field
                  v-model="newItem.qty"
                  label="Ед. измерения"
                  hide-details
                  required
                ></v-text-field>
              </td>
              <td>
                <v-text-field
                  v-model="newItem.price"
                  label="Цена"
                  type="number"
                  hide-details
                  required
                ></v-text-field>
              </td>
              <td>
                {{ newItem.count * newItem.price }}руб.
              </td>
              <td>
                <v-btn @click="addItem">
                  <v-icon>mdi-plus</v-icon>
                </v-btn>
              </td>
            </tr>
          </tbody>
        </v-table>
        <div v-if="error" style="color: red;">{{ error }}</div>
        <v-btn style="margin-top: 1em; float: right" @click="submit">Отправить</v-btn>
      </v-container>
    </v-form>
  </div>
</template>

<script>
import { clone } from 'lodash'

const emptyItem = {
  name: '',
  count: '',
  qty: '',
  price: ''
}
function getBase64(file) {
  const promise = new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function () {
      resolve(reader.result);
    };
    reader.onerror = function (error) {
      console.log('Error: ', error);
      reject(error)
    };
  })
  return promise
}

export default {
  name: 'BillForm',
  data: () => ({
    error: null,
    valid: false,
    form: {
      logo: null,
      providerName: '',
      providerInn: '',
      providerKpp: '',
      providerAddress: '',
      consumerName: '',
      consumerInn: '',
      consumerAddress: '',
      goods: []
    },
    newItem: clone(emptyItem)
  }),
  methods: {
    addItem () {
      this.form.goods.push(clone(this.newItem))
      this.newItem = clone(emptyItem)
    },
    removeItem (index) {
      this.form.goods.splice(index, 1)
    },
    isNumber (event) {
      if (!/\d/.test(event.key)) return event.preventDefault()
    },
    updateLogo (event) {
      if (!event.length) {
        this.form.logo = null
      } else {
        getBase64(event[0]).then((result) => {
          this.form.logo = result
        })
      }
    },
    submit () {
      this.error = null
      fetch('http://localhost:8000/api/document-generator', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(this.form)
      }).then(async (response) => {
        console.log(response)
        if (response.status === 403) {
          const errorText = await response.text()
          this.error = errorText
          return
        }
        const result = await response.blob()
        const url = window.URL.createObjectURL(result)
        window.open(url)
      }).catch((err) => {
        console.error(err)
      })
    }
  },
  computed: {
    total () {
      return this.form.goods.reduce((acc, cur) => acc + cur.count, 0)
    },
    totalSum () {
      return this.form.goods.reduce((acc, cur) => acc + cur.count * cur.price + cur, 0)
    }
  }
}
</script>
