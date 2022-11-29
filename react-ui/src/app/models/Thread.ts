import { User} from "./User"
import { Post } from "./Post"

export interface Thread {
  id: string
  userId: string
  userName: string
  content: string
  createdAt: Date
  posts: Post[]
}

export interface ThreadPostValues {
  threadContect: string
}
