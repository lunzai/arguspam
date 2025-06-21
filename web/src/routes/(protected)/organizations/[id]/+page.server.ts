import { redirect } from "@sveltejs/kit";
import type { PageServerLoad } from "./$types";

export const load: PageServerLoad = async ({ params }) => {
  const { id } = params;

  if (!id) {
    redirect(302, "/organizations");
  }

  return {
    id,
    title: `Organizations - #${id}`,
  };
};