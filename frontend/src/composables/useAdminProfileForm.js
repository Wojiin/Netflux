import { computed, onMounted, reactive, ref, unref } from "vue";
import { useRouter } from "vue-router";
import apiClient, { getErrorMessage, normalizeCollection } from "../api/axios";
import { extractResourceId } from "../utils/apiResource";

const JSON_LD_HEADERS = {
  Accept: "application/ld+json, application/json",
  "Content-Type": "application/ld+json",
};
const MERGE_PATCH_HEADERS = {
  Accept: "application/ld+json, application/json",
  "Content-Type": "application/merge-patch+json",
};

function matchesPerson(person, profile) {
  const personBirthday = person?.birthday ? String(person.birthday).slice(0, 10) : "";
  const profileBirthday = profile?.birthday ? String(profile.birthday).slice(0, 10) : "";

  return (
    person?.firstName === profile?.firstName
    && person?.lastName === profile?.lastName
    && personBirthday === profileBirthday
  );
}

export function useAdminProfileForm(options, id = null) {
  const {
    endpoint,
    resourceName,
    personFlag,
    redirectTo,
  } = options;

  const router = useRouter();
  const loading = ref(false);
  const peopleLoading = ref(false);
  const error = ref("");
  const people = ref([]);
  const resourceId = computed(() => unref(id) || null);
  const isEdit = computed(() => !!resourceId.value);
  const form = reactive({
    personId: "",
  });

  const availablePeople = computed(() =>
    people.value.filter((person) => !person?.[personFlag] || form.personId === getPersonValue(person)),
  );

  async function loadPeople() {
    peopleLoading.value = true;
    error.value = "";

    try {
      const { data } = await apiClient.get("/people", {
        params: {
          itemsPerPage: 100,
          order: { lastName: "asc", firstName: "asc" },
        },
      });

      people.value = normalizeCollection(data);
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      peopleLoading.value = false;
    }
  }

  async function loadResource() {
    if (!isEdit.value) {
      return;
    }

    loading.value = true;
    error.value = "";

    try {
      const { data } = await apiClient.get(`${endpoint}/${resourceId.value}`);
      const matchedPerson = people.value.find((person) => matchesPerson(person, data));

      form.personId = matchedPerson ? getPersonValue(matchedPerson) : "";
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  function buildPayload() {
    return {
      personId: getCurrentPersonId(),
    };
  }

  async function handleSubmit() {
    loading.value = true;
    error.value = "";

    try {
      const payload = buildPayload();

      if (isEdit.value) {
        await apiClient.patch(`${endpoint}/${resourceId.value}`, payload, {
          headers: MERGE_PATCH_HEADERS,
        });
      } else {
        await apiClient.post(endpoint, payload, {
          headers: JSON_LD_HEADERS,
        });
      }

      await router.push(redirectTo);
    } catch (err) {
      error.value = getErrorMessage(err);
    } finally {
      loading.value = false;
    }
  }

  function getPersonValue(person) {
    const personId = extractResourceId(person);
    return personId ? String(personId) : "";
  }

  function getPersonLabel(person) {
    const fullName = [person?.firstName, person?.lastName].filter(Boolean).join(" ").trim();
    const tags = [
      person?.isActor ? "Acteur" : "",
      person?.isDirector ? "Réalisateur" : "",
    ].filter(Boolean);

    return tags.length ? `${fullName} (${tags.join(" / ")})` : fullName;
  }

  function getCurrentPersonId() {
    const personId = Number(form.personId);
    return Number.isInteger(personId) && personId > 0 ? personId : null;
  }

  onMounted(async () => {
    await loadPeople();
    await loadResource();
  });

  return {
    resourceName,
    isEdit,
    loading,
    peopleLoading,
    error,
    form,
    availablePeople,
    getPersonValue,
    getPersonLabel,
    getCurrentPersonId,
    handleSubmit,
  };
}
